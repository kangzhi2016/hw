<?php
include "lexer.php";
include "parser.php";


function p($data = '')
{
    echo "<pre>";
    var_dump($data);
    echo "</pre>";
}

function testLexer($input, $expect) {
    $lexer = new Lexer($input);
    $tokens = [];

    while (($tok = $lexer->nextToken())['type'] != 'eof') {
        $tokens[] = $tok;
    }

    p($tokens);
    if ($tokens != $expect) {
        echo "expect token is:";
        p($expect);
        echo " but given:";
        p($tokens);
        exit();
    }
}


function testParse($input)
{
    $lexer = new Lexer($input);
    $parse = new Parser($lexer);

    p('parse...');
    try{
        while (($tok = $parse->parse()) != array()) {
//            p('parse...');
        }
    }catch (Exception $e)
    {
        p($e->getMessage());
    }

    p('parse over');
}


//$json = 'let aa="hello world"
//         echo aa ';
//p ($json);


$json = file_get_contents("hello.hw");
//$json = file_get_contents("str.hw");
//p($json);
//$exp_lexer = [
//    ['type' => 'kw', 'literal' => 'let'],
//    ['type' => 'var', 'literal' => 'aa'],
//    ['type' => '=', 'literal' => '='],
//    ['type' => 'str', 'literal' => 'hello world'],
//    ['type' => 'kw', 'literal' => 'echo'],
//    ['type' => 'var', 'literal' => 'aa']
//];
//
//testLexer($json, $exp_lexer);
//
//print "lexer test pass\n";


testParse($json);

//print "parse test pass\n";

