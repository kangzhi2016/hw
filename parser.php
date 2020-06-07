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
        switch ($this->curTokenType())
        {
            case 'eof':
                return $this->ast;
            case 'kw':
                $this->parseKw();
                return array();
            default:
                p($this->curToken);
                p($this->nextToken);
                $this->throw_error('不知道啥情况', 'parse');
        }
    }

    private function parseKw()
    {
//        p(json_encode($this->curToken));
        if ($this->curTokenLiteral() == 'let')
        {
            $this->nextToken();
            $this->parseAssign();
        }
        elseif ($this->curTokenLiteral() == 'echo')
        {
            $this->nextToken();
            $this->parseEcho();
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
        $this->ast['child'][] = $this->makeAst('assign', $assignChild);
//p(json_encode($this->curToken));
    }

    private function parseEcho()
    {
        $echoChild = $this->parseGeneralExpr();
        $this->ast['child'][] = $this->makeAst('echo', $echoChild);
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
            $this->nextToken();
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
        throw new Exception($func.' '.$msg);
    }
    private function throw_error_info($curFunc, $exceptType, $curType)
    {
        throw new Exception($curFunc.' error, expect type is '.$exceptType.', but give type is '.$curType);
    }
}