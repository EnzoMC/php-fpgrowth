<?php


namespace EnzoMC\PhpFPGrowth;


class FPGrowth
{
    protected $support = 3;
    protected $confidence = 0.7;

    private $patterns;
    private $rules;

    /**
     * @return mixed
     */
    public function getSupport()
    {
        return $this->support;
    }

    /**
     * @param mixed $support
     */
    public function setSupport($support)
    {
        $this->support = $support;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConfidence()
    {
        return $this->confidence;
    }

    /**
     * @param mixed $confidence
     */
    public function setConfidence($confidence)
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
     * @param $support 1, 2, 3 ...
     * @param $confidence 0 ... 1
     */
    public function __construct($support, $confidence)
    {
        $this->support = $support;
        $this->confidence = $confidence;
    }

    /**
     * Do algorithm
     * @param $transactions
     */
    public function run($transactions)
    {
        $this->patterns = $this->findFrequentPatterns($transactions, $this->support);
        $this->rules = $this->generateAssociationRules($this->patterns, $this->confidence);
    }

    protected function findFrequentPatterns($transactions, $support_threshold)
    {
        $tree = new FPTree($transactions, $support_threshold, null, null);
        return $tree->minePatterns($support_threshold);
    }

    protected function generateAssociationRules($patterns, $confidence_threshold)
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
                        $confidence = (floatval($upper_support) / $lower_support);
                        if ($confidence >= $confidence_threshold) {
                            $rules[$antecedentStr] = [$consequentStr, $confidence];
                        }
                    }
                }
            }
        }
        return $rules;
    }

    public static function iter($var)
    {

        switch (true) {
            case $var instanceof \Iterator:
                return $var;

            case $var instanceof \Traversable:
                return new \IteratorIterator($var);

            case is_string($var):
                $var = str_split($var);

            case is_array($var):
                return new \ArrayIterator($var);

            default:
                $type = gettype($var);
                throw new \InvalidArgumentException("'$type' type is not iterable");
        }

        return;
    }

    public static function combinations($iterable, $r)
    {
        $pool = is_array($iterable) ? $iterable : iterator_to_array(self::iter($iterable));
        $n = sizeof($pool);

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
