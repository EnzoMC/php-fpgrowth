<?php

declare(strict_types=1);

namespace EnzoMC\PhpFPGrowth;

use drupol\phpermutations\Generators\Combinations;

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
        foreach (array_keys($patterns) as $pattern) {
            $itemSet = explode(',', $pattern);
            $upperSupport = $patterns[$pattern];
            for ($i = 1; $i < count($itemSet); $i++) {
                $combinations = new Combinations($itemSet, $i);
                foreach ($combinations->generator() as $antecedent) {
                    sort($antecedent);
                    $antecedentStr = implode(',', $antecedent);
                    $consequent = array_diff($itemSet, $antecedent);
                    sort($consequent);
                    $consequentStr = implode(',', $consequent);
                    if (isset($patterns[$antecedentStr])) {
                        $lowerSupport = $patterns[$antecedentStr];
                        $confidence = floatval($upperSupport) / $lowerSupport;
                        if ($confidence >= $this->confidence) {
                            $rules[] = [$antecedentStr, $consequentStr, $confidence];
                        }
                    }
                }
            }
        }
        return $rules;
    }
}
