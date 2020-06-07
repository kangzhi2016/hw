<?php

include "lexer.php";
include "parser.php";
include "exec.php";

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

//$input = file_get_contents("hw/hello.hw");
//$input = file_get_contents("hw/str.hw");
//$input = file_get_contents("hw/expr.hw");
$input = file_get_contents("hw/func.hw");
p($input);

$lexer = new Lexer($input);
$parse = new Parser($lexer);

try{
    while (($asts = $parse->parse()) == array()) {
        echo '';
    }
}catch (Exception $e)
{
    p($e->getMessage());
}

//p($asts);
//echo(json_encode($asts));
//exit();

$exec = new Exec();

try {
    $exec->compileAst($asts);
} catch (Exception $e) {
    p($e->getMessage());
}


//p($varTable);