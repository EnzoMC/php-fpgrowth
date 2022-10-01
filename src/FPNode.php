<?php

declare(strict_types=1);

namespace EnzoMC\PhpFPGrowth;

class FPNode
{
    public $value;
    public int $count;
    public ?FPNode $parent = null;
    public ?FPNode $link = null;
    /** @var FPNode[] */
    public array $children = [];

    /**
     * Create the node.
     * @param mixed $value
     * @param int $count
     * @param FPNode|null $parent
     */
    public function __construct($value, int $count, ?FPNode $parent)
    {
        $this->value = $value;
        $this->count = $count;
        $this->parent = $parent;
        $this->link = null;
        $this->children = [];
    }

    /**
     * Check if node has a particular child node.
     * @param mixed $value
     * @return bool
     */
    public function hasChild($value): bool
    {
        foreach ($this->children as $node) {
            if ($node->value == $value) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return a child node with a particular value.
     * @param $value
     * @return FPNode|null
     */
    public function getChild($value): ?FPNode
    {
        foreach ($this->children as $node) {
            if ($node->value == $value) {
                return $node;
            }
        }
        return null;
    }

    /**
     * Add a node as a child node.
     * @param $value
     * @return FPNode
     */
    public function addChild($value): FPNode
    {
        $child = new FPNode($value, 1, $this);
        $this->children[] = $child;
        return $child;
    }
}
