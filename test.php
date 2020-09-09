<?php
include "lexer.php";
include "parser.php";


function p($data = '')
{
    echo "<pre>";
    var_dump($data);
    echo "</pre>";
}

function pt($data)
{
    p($data);
    exit();
}

//pt((int)'a');
//pt(strtolower('>'));

function testLexer($input, $expect) {
    $lexer = new Lexer($input);
    $tokens = [];

    try{
        while (($tok = $lexer->nextToken())['type'] != 'eof') {
            $tokens[] = $tok;
        }
    }catch (Exception $e)
    {
        p($e->getMessage());
    }

    if ($tokens != $expect) {
        echo "expect token is:";
        echo json_encode($expect);
        echo "<br>";
        echo "givens token is:";
        echo json_encode($tokens);
        exit();
    }
}


function testParse($input, $expect)
{
    $lexer = new Lexer($input);
    $parse = new Parser($lexer);

    p('parse...');
    try{
        while (($tokens = $parse->parse()) == array()) {}
    }catch (Exception $e)
    {
        p($e->getMessage());
    }

    if ($expect && $tokens != $expect)
    {
        echo "expect token is:";
        echo json_encode($expect);
        echo "\n";
        echo "</pre>givens token is:";
        echo json_encode($tokens);
        exit();
    }
}


//$json = 'let aa="hello world"
//         echo aa ';
//p ($json);


//$json = file_get_contents("hw/hello.hw");
//$json = file_get_contents("hw/str.hw");
//$json = file_get_contents("hw/expr.hw");
//$json = file_get_contents("hw/func.hw");
//$json = file_get_contents("hw/ifelse.hw");
//$file = isset($argv[1])?$argv[1]:"hw/ifelse.hw";

$file = "sql/simple.sql";
if (isset($argv[1]) && $argv[1])
{
    $file = $argv[1];
}
elseif (isset($_GET['f']) && $_GET['f'])
{
    $file = $_GET['f'];
}

$json = file_get_contents($file);
//p($json);
$exp_lexer = [
    //select valid_etime,id from t
    ['type' => 'select', 'literal' => 'select'],
    ['type' => 'var', 'literal' => 'valid_etime'],
    ['type' => ',', 'literal' => ','],
    ['type' => 'var', 'literal' => 'id'],
    ['type' => 'from', 'literal' => 'from'],
    ['type' => 'var', 'literal' => 't'],

    //where
    ['type' => 'where', 'literal' => 'where'],
    //id > 10
    ['type' => 'var', 'literal' => 'id'],
    ['type' => '>', 'literal' => '>'],
    ['type' => 'num', 'literal' => 10],

    //or (price+2 = 100 or prize-2 = 50)
//    ['type' => 'kw', 'literal' => 'or'],
//    ['type' => '(', 'literal' => '('],
//    ['type' => 'var', 'literal' => 'price'],
//    ['type' => '+', 'literal' => '+'],
//    ['type' => 'num', 'literal' => '2'],
//    ['type' => '=', 'literal' => '='],
//    ['type' => 'num', 'literal' => '100'],
//    ['type' => 'kw', 'literal' => 'or'],
//    ['type' => 'var', 'literal' => 'price'],
//    ['type' => '-', 'literal' => '-'],
//    ['type' => 'num', 'literal' => '2'],
//    ['type' => '=', 'literal' => '='],
//    ['type' => 'num', 'literal' => '50'],
//    ['type' => ')', 'literal' => ')'],
//    //and (valid_etime*3 < "2020-10-01" or valid_etime/2 > "2020-10-01" or id = 10 and price < 10)
//    ['type' => 'kw', 'literal' => 'and'],
//    ['type' => '(', 'literal' => '('],
//    ['type' => 'var', 'literal' => 'valid_etime'],
//    ['type' => '*', 'literal' => '*'],
//    ['type' => 'num', 'literal' => '3'],
//    ['type' => '<', 'literal' => '<'],
//    ['type' => 'str', 'literal' => '2020-10-01'],
//    ['type' => 'kw', 'literal' => 'or'],
//    ['type' => 'var', 'literal' => 'valid_etime'],
//    ['type' => '/', 'literal' => '/'],
//    ['type' => 'num', 'literal' => '2'],
//    ['type' => '>', 'literal' => '>'],
//    ['type' => 'str', 'literal' => '2020-10-01'],
//    ['type' => 'kw', 'literal' => 'or'],
//    ['type' => 'var', 'literal' => 'id'],
//    ['type' => '=', 'literal' => '='],
//    ['type' => 'num', 'literal' => '10'],
//    ['type' => 'kw', 'literal' => 'and'],
//    ['type' => 'var', 'literal' => 'price'],
//    ['type' => '<', 'literal' => '<'],
//    ['type' => 'num', 'literal' => '10'],
//    ['type' => ')', 'literal' => ')'],
    //order by
    //id,price desc
    ['type' => 'order', 'literal' => 'order'],
    ['type' => 'by', 'literal' => 'by'],
    ['type' => 'var', 'literal' => 'id'],
    ['type' => ',', 'literal' => ','],
    ['type' => 'var', 'literal' => 'price'],
    ['type' => 'desc', 'literal' => 'desc'],
    //limit
    //0,10
    ['type' => 'limit', 'literal' => 'limit'],
    ['type' => 'num', 'literal' => 0],
    ['type' => ',', 'literal' => ','],
    ['type' => 'num', 'literal' => 10],

];

//echo json_encode($exp_lexer);
//testLexer($json, $exp_lexer);
//print "lexer test pass\n";
//exit();


$exp_parse = [
    'kind' => 'root', 'child' => [
        //select valid_etime,id
        ['kind' => 'select', 'attr' => 'select', 'child' => [
            ['kind' => '*', 'child' => '*'],
            ['kind' => 'var', 'child' => 'id'],
        ]],

        //from t
        ['kind' => 'from', 'attr' => 'from', 'child' => ['kind' => 'var', 'child' => 't']],

        //where
        //    id > 10
//        ['kind' => 'where', 'attr' => 'where', 'child' => [
//            'kind' => 'exp', 'attr' => '>', 'child' => [
//                ['kind' => 'exp', 'attr' => '+', 'child' => [
//                    ['kind' => 'var', 'child' => 'id'],
//                    ['kind' => 'num', 'child' => 1]
//                ]],
//                ['kind' => 'num', 'child' => 10]
//            ]
//        ]],

        //10 * (2-1)
//        ['kind' => 'where', 'attr' => 'where', 'child' => [
//            'kind' => 'exp', 'attr' => '*', 'child' => [
//                ['kind' => 'num', 'child' => 10],
//                ['kind' => 'exp', 'attr' => '-', 'child' => [
//                    ['kind' => 'num', 'child' => 2],
//                    ['kind' => 'num', 'child' => 1]
//                ]],
//            ]
//        ]],

        //10 * (2-1) and id > 10
        ['kind' => 'where', 'attr' => 'where', 'child' => [
            'kind' => 'exp', 'attr' => 'and', 'child' => [
                ['kind' => 'exp', 'attr' => '*', 'child' => [
                    ['kind' => 'num', 'child' => 10],
                    ['kind' => 'exp', 'attr' => '-', 'child' => [
                        ['kind' => 'num', 'child' => 2],
                        ['kind' => 'num', 'child' => 1]
                    ]],
                ]],
                ['kind' => 'exp', 'attr' => '>', 'child' => [
                    ['kind' => 'var', 'child' => 'id'],
                    ['kind' => 'num', 'child' => 10]
                ]]
            ]
        ]],


        //where
        //    id > 10
        //    or (price+2 = 100 or price-2 = 50)
        //    and (valid_etime*3 < "2020-10-01" or valid_etime/2 > "2020-10-01" or id = 10 and price < 10)
//        ['kind' => 'where', 'attr' => 'where', 'child' => [
//            ['kind' => 'exp', 'attr' => 'or', 'child' => [
//                //id > 10
//                [ 'kind' => 'exp', 'attr' => '>', 'child' => [
//                    ['kind' => 'var', 'child' => 'id'],
//                    ['kind' => 'num', 'child' => '10'],
//                ]],
//                //(price+2 = 100 or price-2 = 50) and (valid_etime*3 < "2020-10-01" or valid_etime/2 > "2020-10-01" or id = 10 and price < 10)
//                ['kind' => 'exp', 'attr' => 'and', 'child' => [
//                    //price+2 = 100 or price-2 = 50
//                    ['kind' => 'exp', 'attr' => 'or', 'child' => [
//                        //price+2 = 100
//                        ['kind' => 'exp', 'attr' => '=', 'child' => [
//                            ['kind' => 'exp', 'attr' => '+', 'child' => [
//                                ['kind' => 'var', 'child' => 'price'],
//                                ['kind' => 'num', 'child' => '2']
//                            ]],
//                            ['kind' => 'num', 'child' => '100']
//                        ]],
//                        //price-2 = 50
//                        ['kind' => 'exp', 'attr' => '=', 'child' => [
//                            ['kind' => 'exp', 'attr' => '-', 'child' => [
//                                ['kind' => 'var', 'child' => 'price'],
//                                ['kind' => 'num', 'child' => '2']
//                            ]],
//                            ['kind' => 'num', 'child' => '50']
//                        ]]
//                    ]],
//                    //valid_etime*3 < "2020-10-01" or valid_etime/2 > "2020-10-01" or id = 10 and price < 10
//                    ['kind' => 'exp', 'attr' => 'or', 'child' => [
//                        //valid_etime*3 < "2020-10-01" or valid_etime/2 > "2020-10-01"
//                        ['kind' => 'exp', 'attr' => 'or', 'child' => [
//                            //valid_etime*3 < "2020-10-01"
//                            ['kind' => 'exp', 'attr' => '<', 'child' => [
//                                //valid_etime*3
//                                ['kind' => 'exp', 'attr' => '*', 'child' => [
//                                    ['kind' => 'var', 'child' => 'valid_etime'],
//                                    ['kind' => 'num', 'child' => '3']
//                                ]],
//                                ['kind' => 'str', 'child' => '2020-10-01']
//                            ]],
//                            //valid_etime/2 > "2020-10-01"
//                            ['kind' => 'exp', 'attr' => '>', 'child' => [
//                                //valid_etime/2
//                                ['kind' => 'exp', 'attr' => '/', 'child' => [
//                                    ['kind' => 'var', 'child' => 'valid_etime'],
//                                    ['kind' => 'num', 'child' => '2']
//                                ]],
//                                ['kind' => 'str', 'child' => '2020-10-01']
//                            ]]
//                        ]],
//                        //id = 10 and price < 10
//                        ['kind' => 'exp', 'attr' => 'and', 'child' => [
//                            ['kind' => 'exp', 'attr' => '=', 'child' => [
//                                ['kind' => 'var', 'child' => 'id'],
//                                ['kind' => 'num', 'child' => '10']
//                            ]],
//                            ['kind' => 'exp', 'attr' => '>', 'child' => [
//                                ['kind' => 'var', 'child' => 'price'],
//                                ['kind' => 'num', 'child' => '10']
//                            ]]
//                        ]]
//                    ]]
//                ]],
//            ]],
//        ]],

        //order by id desc, price
        ['kind' => 'order_by', 'attr' => 'order_by', 'child' => [
            ['kind' => 'order_by_group', 'attr' => 'desc', 'child' => ['kind' => 'var', 'child' => 'id']],
            ['kind' => 'order_by_group', 'attr' => 'asc', 'child' => ['kind' => 'var', 'child' => 'price']]
        ]],

        //limit 0,10
//        ['kind' => 'limit', 'attr' => 'limit', 'child' => [
//            ['kind' => 'row', 'child' => 0],
//            ['kind' => 'offset', 'child' => 10],
//        ]],

    ]
];

//echo json_encode($exp_parse);
testParse($json, $exp_parse);
print "parse test pass\n";
