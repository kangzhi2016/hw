<?php

class Parser
{
    private $lexer;

    // 当前token
    private $curToken;

    // 下一个token
    private $nextToken;

    //变量存储表
    private $varTable = array();

    //方法表
    private $funcTable = array();

    //语法树
    private $ast = array();

    public function __construct(Lexer $lexer)
    {
        $this->lexer = $lexer;
        $this->nextToken();
        $this->nextToken();

        $this->ast = $this->makeAst('root', array());
    }

    // 主方法
    public function parse()
    {
        while ( !$this->curTokenTypeIs('eof') )
        {
            $this->ast['child'][] = $this->parseKw();
            $this->nextToken();
        }

        return $this->ast;
    }

    private function parseKw()
    {
        if ($this->curTokenLiteral() == 'let')
        {
            $this->nextToken();
            return $this->parseAssign();
        }
        elseif ($this->curTokenLiteral() == 'echo')
        {
            $this->nextToken();
            return $this->parseEcho();
        }
        elseif ($this->curTokenLiteral() == 'func')
        {
            $this->nextToken();
            return $this->parseFunc();
        }
        elseif ($this->curTokenTypeIs('var') && $this->nextTokenTypeIs('('))
        {
            return $this->parseCall();
        }
        elseif ($this->curTokenLiteral() == 'if')
        {
            $this->nextToken();
            return $this->parseIf();
        }
    }

    private function parseAssign()
    {
        if ( ! $this->curTokenTypeIs('var') )
        {
            $this->throw_error_info(__FUNCTION__, 'var', json_encode($this->curToken));
        }

        $assignChild = array();

        $varLiteral = $this->curTokenLiteral();
        $this->varTable[$varLiteral] = '';
        $assignChild[] = $this->makeAst('var', $varLiteral);

        $this->nextToken();
        $this->expectCurTokenType('=');
        $assignChild[] = $this->makeAst('=', '=');

        $assignChild[] = $this->parseGeneralExpr();
        return $this->makeAst('assign', $assignChild);
    }

    private function parseEcho()
    {
        $echoChild = $this->parseGeneralExpr();
        return $this->makeAst('echo', $echoChild);
    }

    private function parseFunc()
    {
        if ( ! $this->curTokenTypeIs('var') )
        {
            $this->throw_error_info(__FUNCTION__, 'var', json_encode($this->curToken));
        }

        $funcChild = array();

        $varLiteral = $this->curTokenLiteral();
        $this->funcTable[] = $varLiteral;
        $funcChild[] = $this->makeAst('var', $varLiteral);

        $this->nextToken();

        $paraChild = $this->parseParas();
        $funcChild[] = $this->makeAst('paras', $paraChild);

        $paraStmt = $this->parseStmt();
        $funcChild[] = $this->makeAst('stmt', $this->makeAst('top', $paraStmt));

        return $this->makeAst('func', $funcChild);
    }

    private function parseParas()
    {
        if ( ! $this->curTokenTypeIs('(') )
        {
            $this->throw_error_info(__FUNCTION__, '(', json_encode($this->curToken));
        }

        $paraChild = array();

        $this->nextToken(); //(

        while ( !$this->curTokenTypeIs(')') )
        {
            $paraChild[] = $this->makeAst($this->curTokenType(), $this->curTokenLiteral());
            $this->nextToken();
        }

        $this->nextToken(); //)

        return $paraChild;
    }

    private function parseStmt($needNext=false)
    {
        if ( ! $this->curTokenTypeIs('{') )
        {
            $this->throw_error_info(__FUNCTION__, '{', json_encode($this->curToken));
        }

        $stmtChild = array();

        $this->nextToken(); //{

        while ( !$this->curTokenTypeIs('}') )
        {
            $stmtChild[] = $this->parseKw();
            $this->nextToken();
        }

        if ($needNext)
        {
            $this->nextToken(); //}
        }

        return $stmtChild;
    }

    private function parseCall()
    {
        $varLiteral = $this->curTokenLiteral();

        if ( !in_array($varLiteral, $this->funcTable) )
        {
            $this->throw_error("func {$varLiteral} undefined");
        }

        $callChild[] = $this->makeAst('var', $varLiteral);

        $this->nextToken(); //var
        $callChild[] = $this->makeAst('paras', $this->parseParas());

        return $this->makeAst('call', $callChild);
    }

    private function parseIf($cond=array(), $stmt=array(), $condNum = 1)
    {
        $ifChild = array();

        $cond['cond'.$condNum] = $this->parseIfCondition();
//        $stmt['cond'.$condNum] = $this->parseStmt(true);
        $paraStmt = $this->parseStmt(true);
        $stmt['cond'.$condNum] = $this->makeAst('top', $paraStmt);
//        $this->nextToken();

        if ($this->curTokenLiteral() == 'else' || $this->curTokenLiteral() == 'elseif' || ($this->curTokenLiteral() == 'else' && $this->nextToken['literal'] == 'if') )
        {
            $this->nextToken();
            return $this->parseIf($cond, $stmt, ++$condNum);
        }

        $ifChild['cond'] = $cond;
        $ifChild['stmt'] = $stmt;
        return $this->makeAst('if', $ifChild);
    }

    private function parseIfCondition()
    {
        $condChild = array();

        if ($this->curTokenTypeIs('{'))
        {
            return $condChild;
        }

        if ( ! $this->curTokenTypeIs('(') )
        {
            $this->throw_error_info(__FUNCTION__, '(', json_encode($this->curToken));
        }

        p($this->curToken);
        $this->nextToken(); //(
        p($this->curToken);

        while ( !$this->curTokenTypeIs(')') )
        {
//            p($this->curToken);
            $condChild[] = $this->makeAst($this->curTokenType(), $this->curTokenLiteral());
            $this->nextToken();
        }

        $this->nextToken(); //)
//p($condChild);
        return $condChild;
    }

    private function parseGeneralExpr()
    {
        if ($this->IsOperator($this->nextToken['type']))
        {
            return $this->makeAst('exp', $this->parseExpr(0));
        }
        else
        {
            $astArr = $this->makeAst($this->curTokenType(), $this->curTokenLiteral());
//            $this->nextToken();
            return $astArr;
        }
    }

    public function parseExpr($precedence)
    {
        if ($this->curTokenTypeIs('num'))
        {
            $left = (int)$this->curTokenLiteral();
            $this->nextToken();
        }
        elseif ($this->curTokenTypeIs('var'))
        {
            $left = $this->curTokenLiteral();
            $this->nextToken();
        }

        while ( !$this->curTokenTypeIs('kw') && $precedence < $this->curPrecedence()) {
            $left = $this->parseInfixExpr($left);
        }
//p($left);
        return $left;
    }
    private function parseInfixExpr($left)
    {
        $op = $this->curTokenLiteral();
        $precedence = $this->curPrecedence();
        $this->nextToken();
        return ['left' => $left, 'op' => $op, 'right' => $this->parseExpr($precedence)];
    }
    private function curPrecedence()
    {
        $precedences = [
            '+' => 1,
            '-' => 1,
            '*' => 2,
            '/' => 2
        ];

        return $precedences[$this->curTokenType()] ?? 0;
    }
    private function IsOperator($tokenType)
    {
        $operators = array('+', '-', '*', '/');
        return in_array($tokenType, $operators);
    }

    // 当前token的 类型
    private function curTokenType()
    {
        return $this->curToken['type'];
    }

    private function curTokenLiteral()
    {
        return $this->curToken['literal'];
    }

    private function curTokenTypeIs($tokenType)
    {
        return $this->curToken['type'] == $tokenType;
    }

    private function nextTokenTypeIs($tokenType)
    {
        return $this->nextToken['type'] == $tokenType;
    }

    // 下一个token的 type是不是期望的type，如果是，吃掉，如果不是，报错
    private function expectNextTokenType($tokenType)
    {
        if ($this->nextToken['type'] == $tokenType)
        {
            $this->nextToken();
            return;
        }

        $this->throw_error_info('expectNextTokenType', $tokenType, $this->nextToken['type']);
    }

    // 当前token的 type是不是期望的type，如果是，吃掉，如果不是，报错
    private function expectCurTokenType($tokenType)
    {
        if ($this->curTokenType() == $tokenType)
        {
            $this->nextToken();
            return;
        }

        $this->throw_error_info('expectCurTokenType', $tokenType, json_encode($this->curToken));
    }

    private function nextToken()
    {
        $this->curToken = $this->nextToken;
        $this->nextToken = $this->lexer->nextToken();
//        p(json_encode($this->nextToken));
    }

    private function makeAst($kind, $child)
    {
        return ['kind' => $kind, 'child' => $child];
    }

    private function assignAstChild($child)
    {
        $this->ast['child'][] = $child;
    }

    private function throw_error($msg, $func='')
    {
//        throw new Exception($func.' '.$msg);
        pt($func.' '.$msg);
    }
    private function throw_error_info($curFunc, $exceptType, $curType)
    {
//        throw new Exception($curFunc.' error, expect type is '.$exceptType.', but give type is '.$curType);
        pt($curFunc.' error, expect type is '.$exceptType.', but give type is '.$curType);
    }
}