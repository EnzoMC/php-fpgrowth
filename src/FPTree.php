<?php

declare(strict_types=1);

namespace EnzoMC\PhpFPGrowth;

use drupol\phpermutations\Generators\Combinations;

class FPTree
{
    /** @var array<string,int> */
    private array $frequent;

    /** @var array<string,FPNode> */
    private array $headers;

    private FPNode $root;

    /**
     * Initialize the tree.
     * @param array $transactions
     * @param int $threshold
     * @param $root_value
     * @param int $root_count
     */
    public function __construct(array $transactions, int $threshold, $root_value, int $root_count)
    {
        $this->frequent = $this->findFrequentItems($transactions, $threshold);
        $this->headers = $this->buildHeaderTable();
        $this->root = $this->buildFPTree($transactions, $root_value, $root_count, $this->frequent);
    }

    /**
     * Create a dictionary of items with occurrences above the threshold.
     * @param array $transactions
     * @param int $threshold
     * @return array<string,int>
     */
    protected function findFrequentItems(array $transactions, int $threshold): array
    {
        $frequentItems = [];
        foreach ($transactions as $transaction) {
            foreach ($transaction as $item) {
                if (array_key_exists($item, $frequentItems)) {
                    $frequentItems[$item] += 1;
                } else {
                    $frequentItems[$item] = 1;
                }
            }
        }

        foreach (array_keys($frequentItems) as $key) {
            if ($frequentItems[$key] < $threshold) {
                unset($frequentItems[$key]);
            }
        }

        arsort($frequentItems);
        return $frequentItems;
    }

    /**
     * Build the header table.
     * @return array<string,null|FPNode>
     */
    protected function buildHeaderTable(): array
    {
        $headers = [];
        foreach (array_keys($this->frequent) as $key) {
            $headers[$key] = null;
        }
        return $headers;
    }

    /**
     * Build the FP tree and return the root node.
     * @param $transactions
     * @param $root_value
     * @param $root_count
     * @param $frequent
     * @return FPNode
     */
    protected function buildFPTree($transactions, $root_value, $root_count, &$frequent): FPNode
    {
        $root = new FPNode($root_value, $root_count, null);
        arsort($frequent);
        foreach ($transactions as $transaction) {
            $sorted_items = [];
            foreach ($transaction as $item) {
                if (isset($frequent[$item])) {
                    $sorted_items[] = $item;
                }
            }

            usort($sorted_items, function ($a, $b) use ($frequent) {
                return $frequent[$b] <=> $frequent[$a];
            });

            if (count($sorted_items) > 0) {
                $this->insertTree($sorted_items, $root);
            }
        }
        return $root;
    }

    /**
     * Recursively grow FP tree.
     * @param array $items
     * @param FPNode $node
     */
    protected function insertTree(array $items, FPNode $node): void
    {
        $first = $items[0];
        $child = $node->getChild($first);

        if ($child !== null) {
            $child->count += 1;
        } else {
            // Add new child
            $child = $node->addChild($first);
            // Link it to header structure.
            if ($this->headers[$first] === null) {
                $this->headers[$first] = $child;
            } else {
                $current = $this->headers[$first];
                while ($current->link !== null) {
                    $current = $current->link;
                }
                $current->link = $child;
            }
        }

        // Call function recursively.
        $remaining_items = array_slice($items, 1, null);

        if (count($remaining_items) > 0) {
            $this->insertTree($remaining_items, $child);
        }
    }

    /**
     * If there is a single path in the tree,
     * return true, else return false.
     * @param FPNode $node
     * @return bool
     */
    protected function treeHasSinglePath(FPNode $node): bool
    {
        $num_children = count($node->children);

        if ($num_children > 1) {
            return false;
        }

        if ($num_children === 0) {
            return true;
        }

        return $this->treeHasSinglePath($node->children[array_key_first($node->children)]);
    }

    /**
     * Mine the constructed FP tree for frequent patterns.
     * @param int $threshold
     * @return array<string,int>
     */
    public function minePatterns(int $threshold): array
    {
        if ($this->treeHasSinglePath($this->root)) {
            return $this->generatePatternList();
        }

        return $this->zipPatterns($this->mineSubTrees($threshold));
    }

    /**
     * Append suffix to patterns in dictionary if
     * we are in a conditional FP tree.
     * @param array $patterns
     * @return array<string,int>
     */
    protected function zipPatterns(array $patterns): array
    {
        if ($this->root->value === null) {
            return $patterns;
        }

        // We are in a conditional tree.
        $newPatterns = [];
        foreach (array_keys($patterns) as $strKey) {
            $key = explode(',', $strKey);
            $key[] = $this->root->value;
            sort($key);
            $newPatterns[implode(',', $key)] = $patterns[$strKey];
        }

        return $newPatterns;
    }

    /**
     * Generate a list of patterns with support counts.
     * @return array<string,int>
     */
    protected function generatePatternList(): array
    {
        $patterns = [];
        $items = array_keys($this->frequent);

        // If we are in a conditional tree, the suffix is a pattern on its own.
        if ($this->root->value !== null) {
            $patterns[$this->root->value] = $this->root->count;
        }

        for ($i = 1; $i <= count($items); $i++) {
            $combinations = new Combinations($items,$i);
            foreach ($combinations->generator() as $subset) {
                $pattern = $this->root->value !== null ? array_merge($subset, [$this->root->value]) : $subset;
                sort($pattern);
                $min = PHP_INT_MAX;
                /** @var string $x */
                foreach ($subset as $x) {
                    if ($this->frequent[$x] < $min) {
                        $min = $this->frequent[$x];
                    }
                }
                $patterns[implode(',', $pattern)] = $min;
            }
        }

        return $patterns;
    }

    /**
     * Generate subtrees and mine them for patterns.
     * @param int $threshold
     * @return array
     */
    protected function mineSubTrees(int $threshold): array
    {
        $patterns = [];
        $mining_order = $this->frequent;
        asort($mining_order);
        $mining_order = array_keys($mining_order);

        // Get items in tree in reverse order of occurrences.
        foreach ($mining_order as $item) {
            /** @var FPNode[] $suffixes */
            $suffixes = [];
            $conditional_tree_input = [];
            $node = $this->headers[$item];

            // Follow node links to get a list of all occurrences of a certain item.
            while ($node !== null) {
                $suffixes[] = $node;
                $node = $node->link;
            }

            // For each currence of the item, trace the path back to the root node.
            foreach ($suffixes as $suffix) {
                $frequency = $suffix->count;
                $path = [];
                $parent = $suffix->parent;
                while ($parent->parent !== null) {
                    $path[] = $parent->value;
                    $parent = $parent->parent;
                }
                for ($i = 0; $i < $frequency; $i++) {
                    $conditional_tree_input[] = $path;
                }
            }

            // Now we have the input for a subtree, so construct it and grab the patterns.
            $subtree = new FPTree($conditional_tree_input, $threshold, $item, $this->frequent[$item]);
            $subtree_patterns = $subtree->minePatterns($threshold);

            // Insert subtree patterns into main patterns dictionary.
            foreach (array_keys($subtree_patterns) as $pattern) {
                if (in_array($pattern, $patterns)) {
                    $patterns[$pattern] += $subtree_patterns[$pattern];
                } else {
                    $patterns[$pattern] = $subtree_patterns[$pattern];
                }
            }
        }

        return $patterns;
    }
}
