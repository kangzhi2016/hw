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


//    p($tokens);
    if ($tokens != $expect) {
//        echo "expect token is:";
//        p($expect);
//        echo " but given:";
//        p($tokens);
//        exit();

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
        while (($tokens = $parse->parse()) == array()) {
//            p($tokens);
        }

//        $tokens = $parse->parse();
    }catch (Exception $e)
    {
        p($e->getMessage());
    }

//    p('parse over');
//    p($tokens);
    if ($expect && $tokens != $expect)
    {
        echo "expect token is:";
//        p($expect);
        echo json_encode($expect);
        echo "<br>";
        echo "givens token is:";
//        p($tokens);
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
$json = file_get_contents("hw/func.hw");
p($json);
//$exp_lexer = [
//    ['type' => 'kw', 'literal' => 'let'],
//    ['type' => 'var', 'literal' => 'aa'],
//    ['type' => '=', 'literal' => '='],
//    ['type' => 'str', 'literal' => 'hello world'],
//    ['type' => 'kw', 'literal' => 'echo'],
//    ['type' => 'var', 'literal' => 'aa'],
//
//    ['type' => 'kw', 'literal' => 'let'],
//    ['type' => 'var', 'literal' => 'bb'],
//    ['type' => '=', 'literal' => '='],
//    ['type' => 'str', 'literal' => 'this is var bb'],
//    ['type' => 'kw', 'literal' => 'echo'],
//    ['type' => 'var', 'literal' => 'bb']
//
//    ['type' => 'kw', 'literal' => 'let'],
//    ['type' => 'var', 'literal' => 'aa'],
//    ['type' => '=', 'literal' => '='],
//    ['type' => 'num', 'literal' => '1'],
//    ['type' => '+', 'literal' => '+'],
//    ['type' => 'num', 'literal' => '2'],
//    ['type' => '*', 'literal' => '*'],
//    ['type' => 'num', 'literal' => '3'],
//    ['type' => 'kw', 'literal' => 'echo'],
//    ['type' => 'var', 'literal' => 'aa']
//
//    ['type' => 'kw', 'literal' => 'func'],
//    ['type' => 'var', 'literal' => 'aa'],
//    ['type' => '(', 'literal' => '('],
//    ['type' => ')', 'literal' => ')'],
//    ['type' => '{', 'literal' => '{'],
//    ['type' => 'kw', 'literal' => 'echo'],
//    ['type' => 'str', 'literal' => 'this is func aa'],
//    ['type' => '}', 'literal' => '}'],
//    ['type' => 'var', 'literal' => 'aa'],
//    ['type' => '(', 'literal' => '('],
//    ['type' => ')', 'literal' => ')']
//];
//
//echo json_encode($exp_lexer);
//testLexer($json, $exp_lexer);
//print "lexer test pass\n";

//$exp_parse = [
//    'kind' => 'root', 'child' => [
//        ['kind' => 'assign', 'child' => [
//            ['kind' => 'var', 'child' => 'aa'],
//            ['kind' => '=', 'child' => '='],
//            ['kind' => 'str', 'child' => 'hello world'],
//        ]],
//        ['kind' => 'echo', 'child' => ['kind' => 'var', 'child' => 'aa']],
//    ]
//];

$exp_parse = [
    'kind' => 'root', 'child' => [
//        ['kind' => 'assign', 'child' => [
//            ['kind' => 'var', 'child' => 'aa'],
//            ['kind' => '=', 'child' => '='],
//            ['kind' => 'exp', 'child' => [
//                'left' => 1,
//                'op' => '+',
//                'right' => [
//                    'left' => 2, 'op' => '*', 'right' => 3
//                ]
//            ]],
//        ]],
//        ['kind' => 'echo', 'child' => ['kind' => 'var', 'child' => 'aa']],

        ['kind' => 'func', 'child' => [
            ['kind' => 'var', 'child' => 'aa'],
            ['kind' => 'paras', 'child' => []],
            ['kind' => 'stmt', 'child' => [
                'kind' => 'top', 'child' => [
                    ['kind' => 'echo', 'child' => ['kind' => 'str', 'child' => 'this is func aa']
                ]],
            ]],
        ]],
        ['kind' => 'call', 'child' => ['kind' => 'var', 'child' => 'aa']],
    ]
];

//echo json_encode($exp_parse);

testParse($json, $exp_parse);
print "parse test pass\n";

