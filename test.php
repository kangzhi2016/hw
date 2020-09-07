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
        echo "<br>";
        echo "givens token is:";
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

$file = "sql/test.sql";
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
    ['type' => 'kw', 'literal' => 'select'],
    ['type' => 'var', 'literal' => 'valid_etime'],
    ['type' => ',', 'literal' => ','],
    ['type' => 'var', 'literal' => 'id'],
    ['type' => 'kw', 'literal' => 'from'],
    ['type' => 'var', 'literal' => 't'],

    //where
    ['type' => 'kw', 'literal' => 'where'],
    //id > 10
    ['type' => 'var', 'literal' => 'id'],
    ['type' => '>', 'literal' => '>'],
    ['type' => 'num', 'literal' => '10'],
    //or (price+2 = 100 or prize-2 = 50)
    ['type' => 'kw', 'literal' => 'or'],
    ['type' => '(', 'literal' => '('],
    ['type' => 'var', 'literal' => 'price'],
    ['type' => '+', 'literal' => '+'],
    ['type' => 'num', 'literal' => '2'],
    ['type' => '=', 'literal' => '='],
    ['type' => 'num', 'literal' => '100'],
    ['type' => 'kw', 'literal' => 'or'],
    ['type' => 'var', 'literal' => 'price'],
    ['type' => '-', 'literal' => '-'],
    ['type' => 'num', 'literal' => '2'],
    ['type' => '=', 'literal' => '='],
    ['type' => 'num', 'literal' => '50'],
    ['type' => ')', 'literal' => ')'],
    //and (valid_etime*3 < "2020-10-01" or valid_etime/2 > "2020-10-01" or id = 10 and price < 10)
    ['type' => 'kw', 'literal' => 'and'],
    ['type' => '(', 'literal' => '('],
    ['type' => 'var', 'literal' => 'valid_etime'],
    ['type' => '*', 'literal' => '*'],
    ['type' => 'num', 'literal' => '3'],
    ['type' => '<', 'literal' => '<'],
    ['type' => 'str', 'literal' => '2020-10-01'],
    ['type' => 'kw', 'literal' => 'or'],
    ['type' => 'var', 'literal' => 'valid_etime'],
    ['type' => '/', 'literal' => '/'],
    ['type' => 'num', 'literal' => '2'],
    ['type' => '>', 'literal' => '>'],
    ['type' => 'str', 'literal' => '2020-10-01'],
    ['type' => 'kw', 'literal' => 'or'],
    ['type' => 'var', 'literal' => 'id'],
    ['type' => '=', 'literal' => '='],
    ['type' => 'num', 'literal' => '10'],
    ['type' => 'kw', 'literal' => 'and'],
    ['type' => 'var', 'literal' => 'price'],
    ['type' => '<', 'literal' => '<'],
    ['type' => 'num', 'literal' => '10'],
    ['type' => ')', 'literal' => ')'],
    //order by
    //id,price desc
    ['type' => 'kw', 'literal' => 'order'],
    ['type' => 'kw', 'literal' => 'by'],
    ['type' => 'var', 'literal' => 'id'],
    ['type' => ',', 'literal' => ','],
    ['type' => 'var', 'literal' => 'price'],
    ['type' => 'kw', 'literal' => 'desc'],
    //limit
    //0,10
    ['type' => 'kw', 'literal' => 'limit'],
    ['type' => 'num', 'literal' => '0'],
    ['type' => ',', 'literal' => ','],
    ['type' => 'num', 'literal' => '10'],

];

//echo json_encode($exp_lexer);
//testLexer($json, $exp_lexer);
//print "lexer test pass\n";
//exit();


$exp_parse = [
    'kind' => 'root', 'child' => [
        //select valid_etime,id
        ['kind' => 'select', 'attr' => 'select', 'child' => [
            ['kind' => 'var', 'child' => 'valid_etime'],
            ['kind' => 'var', 'child' => 'id'],
        ]],

        //from t
        ['kind' => 'from', 'attr' => 'from', 'child' => ['kind' => 'var', 'child' => 't']],

        //where
        //    id > 10
        //    or (price+2 = 100 or price-2 = 50)
        //    and (valid_etime*3 < "2020-10-01" or valid_etime/2 > "2020-10-01" or id = 10 and price < 10)
        ['kind' => 'where', 'attr' => 'where', 'child' => [
            ['kind' => 'exp', 'child' => [
                //id > 10
                'left' => [ 'kind' => 'fuhao', 'attr' => '-', 'child' => [
                    ['kind' => 'num', 'child' => '10']
                ]],
                'op' => 'or',
                //(price+2 = 100 or price-2 = 50) and (valid_etime*3 < "2020-10-01" or valid_etime/2 > "2020-10-01" or id = 10 and price < 10)
                'right' => ['kind' => 'exp', 'child' => [
                    //price+2 = 100 or price-2 = 50
                    'left' => ['kind' => 'exp', 'child' => [
                        //price+2 = 100
                        'left' => ['kind' => 'exp', 'child' => [
                            'left' => ['kind' => 'exp', 'child' => [
                                'left' => ['kind' => 'var', 'child' => 'price'],
                                'op' => '+',
                                'right' => ['kind' => 'num', 'child' => '2']
                            ]],
                            'op' => '=',
                            'right' => ['kind' => 'num', 'child' => '10']
                        ]],
                        'op' => 'or',
                        //price-2 = 50
                        'right' => ['kind' => 'exp', 'child' => [
                            'left' => ['kind' => 'exp', 'child' => [
                                'left' => ['kind' => 'var', 'child' => 'price'],
                                'op' => '-',
                                'right' => ['kind' => 'num', 'child' => '2']
                            ]],
                            'op' => '=',
                            'right' => ['kind' => 'num', 'child' => '50']
                        ]]
                    ]],
                    'op' => 'and',
                    //valid_etime*3 < "2020-10-01" or valid_etime/2 > "2020-10-01" or id = 10 and price < 10
                    'right' => ['kind' => 'exp', 'child' => [
                        //valid_etime*3 < "2020-10-01" or valid_etime/2 > "2020-10-01"
                        'left' => ['kind' => 'exp', 'child' => [
                            //valid_etime*3 < "2020-10-01"
                            'left' => ['kind' => 'exp', 'child' => [
                                //valid_etime*3
                                'left' => ['kind' => 'exp', 'child' => [
                                    'left' => ['kind' => 'var', 'child' => 'valid_etime'],
                                    'op' => '*',
                                    'right' => ['kind' => 'num', 'child' => '3']
                                ]],
                                'op' => '<',
                                'right' => ['kind' => 'str', 'child' => '2020-10-01']
                            ]],
                            'op' => 'or',
                            //valid_etime/2 > "2020-10-01"
                            'right' => ['kind' => 'exp', 'child' => [
                                //valid_etime/2
                                'left' => ['kind' => 'exp', 'child' => [
                                    'left' => ['kind' => 'var', 'child' => 'valid_etime'],
                                    'op' => '/',
                                    'right' => ['kind' => 'num', 'child' => '2']
                                ]],
                                'op' => '>',
                                'right' => ['kind' => 'str', 'child' => '2020-10-01']
                            ]]
                        ]],
                        'op' => 'or',
                        //id = 10 and price < 10
                        'right' => ['kind' => 'exp', 'child' => [
                            'left' => ['kind' => 'exp', 'child' => [
                                'left' => ['kind' => 'var', 'child' => 'id'],
                                'op' => '=',
                                'right' => ['kind' => 'num', 'child' => '10']
                            ]],
                            'op' => 'and',
                            'right' => ['kind' => 'exp', 'child' => [
                                'left' => ['kind' => 'var', 'child' => 'price'],
                                'op' => '>',
                                'right' => ['kind' => 'num', 'child' => '10']
                            ]]
                        ]]
                    ]]
                ]],
            ]],
        ]],

        //order by id,price desc
        ['kind' => 'order_by', 'attr' => 'desc', 'child' => [
            ['kind' => 'var', 'child' => 'id'],
            ['kind' => 'var', 'child' => 'price']
        ]],

        //limit 0,10
        ['kind' => 'limit', 'child' => [
            ['kind' => 'row', 'child' => '0'],
            ['kind' => 'offset', 'child' => '10'],
        ]],

    ]
];

//echo json_encode($exp_parse);
//exit();
testParse($json, $exp_parse);
print "parse test pass\n";
