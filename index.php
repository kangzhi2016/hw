<?php
include "lexer.php";
include "parser.php";

$varTable = array();

function p($data = '')
{
    echo "<pre>";
    var_dump($data);
    echo "</pre>";
}

$input = file_get_contents("hello.hw");
//$input = file_get_contents("str.hw");
//$input = file_get_contents("expr.hw");
$lexer = new Lexer($input);
$parse = new Parser($lexer);

try{
    while (($asts = $parse->parse()) == array()) {}
}catch (Exception $e)
{
    p($e->getMessage());
}


//p($asts);

function compileAst($asts, &$varTable)
{
//    p($asts['kind']);
    if ($asts['kind'] == 'root')
    {
        $count_child = count($asts['child']);
        for ($i=0; $i<$count_child; $i++)
        {
            compileAst($asts['child'][$i], $varTable);
        }
    }
    elseif ($asts['kind'] == 'assign')
    {
        $varLiteral = $asts['child'][0]['child'];
        $varVal = $asts['child'][2]['child'];
        $varTable[$varLiteral] = $varVal;
    }
    elseif ($asts['kind'] == 'echo')
    {
//        p($asts);
        if ($asts['child']['kind'] == 'var')
        {
            $varLiteral = $asts['child']['child'];
            if (!isset($varTable[$varLiteral]))
            {
                throw new Exception("var {$varLiteral} undefined ");
            }

            $varVal = $varTable[$varLiteral];
        }
        elseif ($asts['child']['kind'] == 'str' || $asts['child']['kind'] == 'num')
        {
            $varVal = $asts['child']['child'];
        }

//        echo $varVal;
        p($varVal);
    }
}


try {
    compileAst($asts, $varTable);
} catch (Exception $e) {
    p($e->getMessage());
}

//p($varTable);