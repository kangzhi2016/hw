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

//$input = file_get_contents("hello.hw");
//$input = file_get_contents("str.hw");
$input = file_get_contents("expr.hw");
p($input);

$lexer = new Lexer($input);
$parse = new Parser($lexer);

try{
    while (($asts = $parse->parse()) == array()) {}
}catch (Exception $e)
{
    p($e->getMessage());
}

//p($asts);
//echo(json_encode($asts));
//exit();
function compileAst($asts, &$varTable)
{
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
        if ($asts['child'][0]['kind'] != 'var' || $asts['child'][1]['kind'] != '=')
        {
            throw new Exception("assign expression error : ".json_encode($asts));
        }

        $varLiteral = $asts['child'][0]['child'];

        if ($asts['child'][2]['kind'] == 'exp')
        {
            $varVal = parseExp($asts['child'][2]['child']);
        }
        else
        {
            $varVal = $asts['child'][2]['child'];
        }
//        p(json_encode($varVal));
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
        elseif ($asts['child']['kind'] == 'exp')
        {
            $varVal = parseExp($asts['child']['child'], $varTable);
        }

//        echo $varVal;
        p($varVal);
    }
    else
    {
        echo 'unknown kind';
        echo(json_encode($asts));
    }
}

function parseExp($asts, $varTable=array())
{
    $left = $asts['left'];
    $right = $asts['right'];

    if (is_array($left))
    {
        $left = parseExp($left);
    }
    elseif (is_string($left))
    {
        if (!isset($varTable[$left]))
        {
            throw new Exception("var {$left} undefined ");
        }

        $left = $varTable[$left];
    }

    if (is_array($right))
    {
        $right = parseExp($right);
    }
    elseif (is_string($right))
    {
        if (!isset($varTable[$right]))
        {
            throw new Exception("var {$right} undefined ");
        }

        $right = $varTable[$right];
    }

    switch ($asts['op'])
    {
        case '+':
            return $left + $right;
        case '-':
            return $left - $right;
        case '*':
            return $left * $right;
        case '/':
            return $left / $right;
    }
}


try {
    compileAst($asts, $varTable);
} catch (Exception $e) {
    p($e->getMessage());
}

//p($varTable);