# FP-Growth
A PHP implementation of the Frequent Pattern Growth algorithm

## Getting Started
You can install the package with composer:

    composer require enzomc/php-fpgrowth

## Usage

#### Run algorithm

    use EnzoMC\PhpFPGrowth\FPGrowth;
    
    $support = 3;
    $confidence = 0.7;
    
    $fpgrowth = new FPGrowth($support, $confidence);
    
    $transactions = [
        ['M', 'O', 'N', 'K', 'E', 'Y'],
        ['D', 'O', 'N', 'K', 'E', 'Y'],
        ['M', 'A', 'K', 'E'],
        ['M', 'U', 'C', 'K', 'Y'],
        ['C', 'O', 'O', 'K', 'I', 'E']
    ];
    
    $fpgrowth->run($transactions);
    
    $patterns = $fpgrowth->getPatterns();
    $rules = $fpgrowth->getRules();
 

#### Returned results

Patterns returns as array of arrays like:
    
    [
        ['ITEM_1,ITEM_2' => 3],
        ['ITEM_3' => 5],
        ...
    ]
    
Where key is itemset, value is support of that itemset
    

Rules returns as array of arrays like:

    [
        ['ITEM_1,ITEM_2', 'ITEM_3', 0.7],
        ['ITEM_4','ITEM_5', 0.7],
        ...
    ]

Where first value is left path of that rule, second value is right path of that rule and third value is confidence of that rule

#### Result with example transactions

`var_dump($patterns);`

    array(10) {
      ["M"]=>
      int(3)
      ["K,M"]=>
      int(3)
      ["Y"]=>
      int(3)
      ["K,Y"]=>
      int(3)
      ["E,O"]=>
      int(4)
      ["E,K"]=>
      int(4)
      ["E,K,O"]=>
      int(4)
      ["O"]=>
      int(4)
      ["K,O"]=>
      int(4)
      ["K"]=>
      int(5)
    }
    
`var_dump($rules);`

    array(11) {
      [0]=>
      array(3) {
        [0]=>
        string(1) "M"
        [1]=>
        string(1) "K"
        [2]=>
        float(1)
      }
      [1]=>
      array(3) {
        [0]=>
        string(1) "Y"
        [1]=>
        string(1) "K"
        [2]=>
        float(1)
      }
      [2]=>
      array(3) {
        [0]=>
        string(1) "O"
        [1]=>
        string(1) "E"
        [2]=>
        float(1)
      }
      [3]=>
      array(3) {
        [0]=>
        string(1) "K"
        [1]=>
        string(1) "E"
        [2]=>
        float(0.8)
      }
      [4]=>
      array(3) {
        [0]=>
        string(1) "K"
        [1]=>
        string(3) "E,O"
        [2]=>
        float(0.8)
      }
      [5]=>
      array(3) {
        [0]=>
        string(1) "O"
        [1]=>
        string(3) "E,K"
        [2]=>
        float(1)
      }
      [6]=>
      array(3) {
        [0]=>
        string(3) "E,K"
        [1]=>
        string(1) "O"
        [2]=>
        float(1)
      }
      [7]=>
      array(3) {
        [0]=>
        string(3) "E,O"
        [1]=>
        string(1) "K"
        [2]=>
        float(1)
      }
      [8]=>
      array(3) {
        [0]=>
        string(3) "K,O"
        [1]=>
        string(1) "E"
        [2]=>
        float(1)
      }
      [9]=>
      array(3) {
        [0]=>
        string(1) "K"
        [1]=>
        string(1) "O"
        [2]=>
        float(0.8)
      }
      [10]=>
      array(3) {
        [0]=>
        string(1) "O"
        [1]=>
        string(1) "K"
        [2]=>
        float(1)
      }
    }
