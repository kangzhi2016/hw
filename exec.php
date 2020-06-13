<?php

class Exec
{
    private $varTable = array();
    private $funcTable = array();

    function compileAst($asts, $scope='global', $scope_name='')
    {
//        p($asts);
        if ($asts['kind'] == 'root' || $asts['kind'] == 'top')
        {
            $count_child = count($asts['child']);
            for ($i=0; $i<$count_child; $i++)
            {
                $this->compileAst($asts['child'][$i], $scope, $scope_name);
            }
        }
        elseif ($asts['kind'] == 'assign')
        {
            if ($asts['child'][0]['kind'] != 'var' || $asts['child'][1]['kind'] != '=')
            {
                pt("assign expression error : ".json_encode($asts));
            }

            $varLiteral = $asts['child'][0]['child'];

            if ($asts['child'][2]['kind'] == 'exp')
            {
                $varVal = $this->evalExp($asts['child'][2]['child']);
            }
            else
            {
                $varVal = $asts['child'][2]['child'];
            }

            if ($scope == 'global')
            {
                $this->varTable[$varLiteral] = $varVal;
            }
            elseif ($scope == 'func')
            {
                $this->funcTable[$scope_name]['varTable'][$varLiteral] = $varVal;
            }

        }
        elseif ($asts['kind'] == 'echo')
        {
//            p($asts);
            if ($asts['child']['kind'] == 'var')
            {
                $varLiteral = $asts['child']['child'];

                if ($scope == 'global')
                {
                    if (!isset($this->varTable[$varLiteral]))
                    {
                        pt("var {$varLiteral} undefined ");
                    }

                    $varVal = $this->varTable[$varLiteral];
                }
                elseif ($scope == 'func')
                {
                    if (!isset($this->funcTable[$scope_name]['varTable'][$varLiteral]))
                    {
                        pt("var {$varLiteral} undefined ");
                    }
                    $varVal = $this->funcTable[$scope_name]['varTable'][$varLiteral];
                }
            }
            elseif ($asts['child']['kind'] == 'str' || $asts['child']['kind'] == 'num')
            {
                $varVal = $asts['child']['child'];
            }
            elseif ($asts['child']['kind'] == 'exp')
            {
                $varVal = $this->evalExp($asts['child']['child']);
            }

            echo $varVal;
//            p($varVal);
        }
        elseif ($asts['kind'] == 'func')
        {
            if ( !isset($asts['child'][0]['kind']) || $asts['child'][0]['kind'] != 'var')
            {
                pt("func defined error: ".json_encode($asts));
            }

            $funcName = $asts['child'][0]['child'];
            if ( isset($this->funcTable[$funcName]) )
            {
                pt("func {$funcName} is already defined ");
            }

            $this->funcTable[$funcName]['paras'] = $asts['child'][1]['child'];
            $this->funcTable[$funcName]['stmt'] = $asts['child'][2]['child'];
        }
        elseif ($asts['kind'] == 'call')
        {
            $this->callFunc($asts['child']);
        }
        else
        {
            echo 'unknown kind';
//            echo(json_encode($asts));
            p($asts);
        }
    }

    private function callFunc($callChild)
    {
        $funcName = $callChild[0]['child'];
        if ( !isset($this->funcTable[$funcName]) )
        {
            pt("func {$funcName} is undefined ");
        }

        $funcChild = $this->funcTable[$funcName];
        $callParas = $callChild[1]['child'];

        if ( !empty($funcChild['paras']) ) //paras
        {
            foreach ($funcChild['paras'] as $key=>$para)
            {
                $this->funcTable[$funcName]['varTable'][$para['child']] = $callParas[$key]['child'];
            }
        }
//        p($funcChild);
        if ( !empty($funcChild['stmt']) ) //stmt
        {
            $this->compileAst($funcChild['stmt'], 'func', $funcName);
        }
    }

    private function evalExp($asts)
    {
        $left = $asts['left'];
        $right = $asts['right'];

        if (is_array($left))
        {
            $left = $this->evalExp($left);
        }
        elseif (is_string($left))
        {
            if (!isset($this->varTable[$left]))
            {
                pt("var {$left} undefined ");
            }

            $left = $this->varTable[$left];
        }

        if (is_array($right))
        {
            $right = $this->evalExp($right);
        }
        elseif (is_string($right))
        {
            if (!isset($this->varTable[$right]))
            {
                pt("var {$right} undefined ");
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



