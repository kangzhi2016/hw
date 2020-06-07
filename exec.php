<?php

//include "lexer.php";
//include "parser.php";

class Exec
{
    private $varTable = array();
    private $funcTable = array();

    function compileAst($asts)
    {
//        p($asts);
        if ($asts['kind'] == 'root' || $asts['kind'] == 'top')
        {
            $count_child = count($asts['child']);
            for ($i=0; $i<$count_child; $i++)
            {
                $this->compileAst($asts['child'][$i]);
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
                $varVal = $this->parseExp($asts['child'][2]['child']);
            }
            else
            {
                $varVal = $asts['child'][2]['child'];
            }
    //        p(json_encode($varVal));
            $this->varTable[$varLiteral] = $varVal;
        }
        elseif ($asts['kind'] == 'echo')
        {
    //        p($asts);
            if ($asts['child']['kind'] == 'var')
            {
                $varLiteral = $asts['child']['child'];
                if (!isset($this->varTable[$varLiteral]))
                {
                    throw new Exception("var {$varLiteral} undefined ");
                }

                $varVal = $this->varTable[$varLiteral];
            }
            elseif ($asts['child']['kind'] == 'str' || $asts['child']['kind'] == 'num')
            {
                $varVal = $asts['child']['child'];
            }
            elseif ($asts['child']['kind'] == 'exp')
            {
                $varVal = $this->parseExp($asts['child']['child']);
            }

    //        echo $varVal;
            p($varVal);
        }
        elseif ($asts['kind'] == 'func')
        {
    //        ['kind' => 'func', 'child' => [
    //            ['kind' => 'var', 'child' => 'aa'],
    //            ['kind' => 'paras', 'child' => []],
    //            ['kind' => 'stmt', 'child' => [
    //                ['kind' => 'echo', 'child' => ['kind' => 'str', 'child' => 'this is func aa']],
    //            ]],
    //        ]],
    //        ['kind' => 'call', 'child' => ['kind' => 'var', 'child' => 'aa']],

            if ( !isset($asts['child'][0]['kind']) || $asts['child'][0]['kind'] != 'var')
            {
                throw new Exception("func defined error: ".json_encode($asts));
            }

            $funcName = $asts['child'][0]['child'];
            if ( isset($this->funcTable[$funcName]) )
            {
                throw new Exception("func {$funcName} is already defined ");
            }

            $this->funcTable[$funcName] = $asts['child'];
        }
        elseif ($asts['kind'] == 'call')
        {
            $this->callFunc($asts['child']['child']);
        }
        else
        {
            echo 'unknown kind';
//            echo(json_encode($asts));
            p($asts);
        }
    }

    private function callFunc($funcName)
    {
        if ( !isset($this->funcTable[$funcName]) )
        {
            throw new Exception("func {$funcName} is undefined ");
        }

        $funcChild = $this->funcTable[$funcName];

        if ( !empty($funcChild[1]) ) //paras
        {

        }

        if ( !empty($funcChild[2]) ) //stmt
        {
            $this->compileAst($funcChild[2]['child']);
        }
    }

    private function parseExp($asts)
    {
        $left = $asts['left'];
        $right = $asts['right'];

        if (is_array($left))
        {
            $left = $this->parseExp($left);
        }
        elseif (is_string($left))
        {
            if (!isset($this->varTable[$left]))
            {
                throw new Exception("var {$left} undefined ");
            }

            $left = $this->varTable[$left];
        }

        if (is_array($right))
        {
            $right = $this->parseExp($right);
        }
        elseif (is_string($right))
        {
            if (!isset($this->varTable[$right]))
            {
                throw new Exception("var {$right} undefined ");
            }

            $right = $this->varTable[$right];
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

    //p($varTable);
}



