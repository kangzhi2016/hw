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

//pt($argv);

function testLexer($input, $expect) {
    $lexer = new Lexer($input);
    $tokens = [];

    try{
        while (($tok = $lexer->nextToken())['type'] != 'eof') {
//            p($tok);
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
//$json = file_get_contents("hw/func.hw");
//$json = file_get_contents("hw/ifelse.hw");
//$file = isset($argv[1])?$argv[1]:"hw/ifelse.hw";

$file = "hw/hello.hw";
if (isset($argv[1]) && $argv[1])
{
    $file = $argv[1];
}
elseif (isset($_GET['f']) && $_GET['f'])
{
    $file = $_GET['f'];
}

$json = file_get_contents($file);
p($json);
$exp_lexer = [
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
    ['type' => 'kw', 'literal' => 'let'],
    ['type' => 'var', 'literal' => 'aa'],
    ['type' => '=', 'literal' => '='],
    ['type' => 'num', 'literal' => '1'],
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

    ['type' => 'kw', 'literal' => 'if'],
    ['type' => '(', 'literal' => '('],
    ['type' => 'var', 'literal' => 'aa'],
    ['type' => '=', 'literal' => '='],
    ['type' => 'num', 'literal' => '1'],
    ['type' => ')', 'literal' => ')'],
    ['type' => '{', 'literal' => '{'],
    ['type' => 'kw', 'literal' => 'echo'],
    ['type' => 'num', 'literal' => '1'],
    ['type' => '}', 'literal' => '}'],
    ['type' => 'kw', 'literal' => 'elseif'],
    ['type' => '(', 'literal' => '('],
    ['type' => 'num', 'literal' => '0'],
    ['type' => ')', 'literal' => ')'],
    ['type' => '{', 'literal' => '{'],
    ['type' => 'kw', 'literal' => 'echo'],
    ['type' => 'num', 'literal' => '2'],
    ['type' => '}', 'literal' => '}'],
    ['type' => 'kw', 'literal' => 'else'],
    ['type' => '{', 'literal' => '{'],
    ['type' => 'kw', 'literal' => 'echo'],
    ['type' => 'num', 'literal' => '3'],
    ['type' => '}', 'literal' => '}'],

];

//echo json_encode($exp_lexer);
testLexer($json, $exp_lexer);
print "lexer test pass\n";
//exit();
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

//        ['kind' => 'func', 'child' => [
//            ['kind' => 'var', 'child' => 'aa'],
//            ['kind' => 'paras', 'child' => [
//                ['kind' => 'var', 'child' => 'bb']
//            ]],
//            ['kind' => 'stmt', 'child' => [
//                'kind' => 'top', 'child' => [
//                    ['kind' => 'echo', 'child' => ['kind' => 'var', 'child' => 'bb']
//                ]],
//            ]],
//        ]],
//        ['kind' => 'call', 'child' => [
//            ['kind' => 'var', 'child' => 'aa'],
//            ['kind' => 'paras', 'child' => [
//                ['kind' => 'num', 'child' => 1]
//            ]],
//        ]],

        ['kind' => 'assign', 'child' => [
            ['kind' => 'var', 'child' => 'aa'],
            ['kind' => '=', 'child' => '='],
            ['kind' => 'num', 'child' => 1],
        ]],
        ['kind' => 'if', 'child' => [
            'cond' => [
                'cond1' => [
                    ['kind' => 'var', 'child' => 'aa'],
                    ['kind' => '>', 'child' => '>'],
                    ['kind' => 'num', 'child' => 0],
                ],
                'cond2' => [
                    ['kind' => 'num', 'child' => 0]
                ],
                'cond3' => [],
            ],
            'stmt' => [
                'cond1' => [
                    'kind' => 'top', 'child' => [
                        ['kind' => 'echo', 'child' => ['kind' => 'num', 'child' => 1]
                    ]]
                ],
                'cond2' => [
                    'kind' => 'top', 'child' => [
                        ['kind' => 'echo', 'child' => ['kind' => 'num', 'child' => 2]
                    ]]
                ],
                'cond3' => [
                    'kind' => 'top', 'child' => [
                        ['kind' => 'echo', 'child' => ['kind' => 'num', 'child' => 3]
                    ]]
                ],
            ]
        ]]
    ]
];

//echo json_encode($exp_parse);

testParse($json, $exp_parse);
print "parse test pass\n";

