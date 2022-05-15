<?php

declare(strict_types=1);

namespace EnzoMC\PhpFPGrowth;

use Generator;

class FPGrowth
{
    protected int $support = 3;
    protected float $confidence = 0.7;

    private $patterns;
    private $rules;

    /**
     * @return int
     */
    public function getSupport(): int
    {
        return $this->support;
    }

    /**
     * @param int $support
     * @return self
     */
    public function setSupport(int $support): self
    {
        $this->support = $support;
        return $this;
    }

    /**
     * @return float
     */
    public function getConfidence(): float
    {
        return $this->confidence;
    }

    /**
     * @param float $confidence
     * @return self
     */
    public function setConfidence(float $confidence): self
    {
        $this->confidence = $confidence;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPatterns()
    {
        return $this->patterns;
    }

    /**
     * @return mixed
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * FPGrowth constructor.
     * @param int $support 1, 2, 3 ...
     * @param float $confidence 0 ... 1
     */
    public function __construct(int $support, float $confidence)
    {
        $this->setSupport($support);
        $this->setConfidence($confidence);
    }

    /**
     * Do algorithm
     * @param array $transactions
     */
    public function run(array $transactions)
    {
        $this->patterns = $this->findFrequentPatterns($transactions);
        $this->rules = $this->generateAssociationRules($this->patterns);
    }

    /**
     * @param array $transactions
     * @return array<string,int>
     */
    protected function findFrequentPatterns(array $transactions): array
    {
        $tree = new FPTree($transactions, $this->support, null, 0);
        return $tree->minePatterns($this->support);
    }

    /**
     * @param array $patterns
     * @return array
     */
    protected function generateAssociationRules(array $patterns): array
    {
        $rules = [];
        foreach (array_keys($patterns) as $itemsetStr) {
            $itemset = explode(',', $itemsetStr);
            $upper_support = $patterns[$itemsetStr];
            for ($i = 1; $i < count($itemset); $i++) {
                foreach (self::combinations($itemset, $i) as $antecedent) {
                    sort($antecedent);
                    $antecedentStr = implode(',', $antecedent);
                    $consequent = array_diff($itemset, $antecedent);
                    sort($consequent);
                    $consequentStr = implode(',', $consequent);
                    if (isset($patterns[$antecedentStr])) {
                        $lower_support = $patterns[$antecedentStr];
                        $confidence = floatval($upper_support) / $lower_support;
                        if ($confidence >= $this->confidence) {
                            $rules[] = [$antecedentStr, $consequentStr, $confidence];
                        }
                    }
                }
            }
        }
        return $rules;
    }

    /**
     * @param array $pool
     * @param int $r
     * @return Generator
     * @todo Move to separate class
     */
    public static function combinations(array $pool, int $r): Generator
    {
        $n = count($pool);

        if ($r > $n) {
            return;
        }

        $indices = range(0, $r - 1);
        yield array_slice($pool, 0, $r);

        for (; ;) {
            for (; ;) {
                for ($i = $r - 1; $i >= 0; $i--) {
                    if ($indices[$i] != $i + $n - $r) {
                        break 2;
                    }
                }

                return;
            }

            $indices[$i]++;

            for ($j = $i + 1; $j < $r; $j++) {
                $indices[$j] = $indices[$j - 1] + 1;
            }

            $row = [];
            foreach ($indices as $i) {
                $row[] = $pool[$i];
            }

            yield $row;
        }
    }
}
