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
        ['ITEM_1,ITEM_2' => ['ITEM_3',0.7]],
        ['ITEM_4' => ['ITEM_5',0.7]],
        ...
    ]

Where keys is left path of rule, value is array, where first item is right path of the rule and second item is confidence of that rule

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

    array(7) {
      ["M"]=>
      array(2) {
        [0]=>
        string(1) "K"
        [1]=>
        float(1)
      }
      ["Y"]=>
      array(2) {
        [0]=>
        string(1) "K"
        [1]=>
        float(1)
      }
      ["O"]=>
      array(2) {
        [0]=>
        string(1) "K"
        [1]=>
        float(1)
      }
      ["K"]=>
      array(2) {
        [0]=>
        string(1) "O"
        [1]=>
        float(0.8)
      }
      ["E,K"]=>
      array(2) {
        [0]=>
        string(1) "O"
        [1]=>
        float(1)
      }
      ["E,O"]=>
      array(2) {
        [0]=>
        string(1) "K"
        [1]=>
        float(1)
      }
      ["K,O"]=>
      array(2) {
        [0]=>
        string(1) "E"
        [1]=>
        float(1)
      }
    }