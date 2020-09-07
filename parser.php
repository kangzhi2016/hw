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
//        $this->nextToken();
//        $this->nextToken();
        $this->curToken = $this->lexer->nextToken();
        $this->nextToken = $this->lexer->nextToken();

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
        if ($this->curTokenLiteral() == 'select')
        {
            $this->nextToken();
            return $this->parseSelect();
        }
        elseif ($this->curTokenLiteral() == 'from')
        {
            $this->nextToken();
            return $this->parseFrom();
        }
        elseif ($this->curTokenLiteral() == 'where')
        {
            $this->nextToken();
            return $this->parseWhere();
        }
        elseif ($this->curTokenLiteral() == 'order' && $this->nextTokenLiteral() == 'by')
        {
            $this->nextToken();
            $this->nextToken();
            return $this->parseOrderBy();
        }
        elseif ($this->curTokenLiteral() == 'limit')
        {
            $this->nextToken();
            return $this->parseLimit();
        }
    }

    private function parseSelect()
    {
        if (!$this->curTokenTypeIs('var') && !$this->curTokenTypeIs('*'))
        {
            $this->throw_error_info(__FUNCTION__, 'var|*', json_encode($this->curToken));
        }

        $selectChild = array();
        $selectChild[] = $this->parseSelectFields();
        return $this->makeAst('select', $selectChild);
    }

    private function parseSelectFields()
    {
        $fieldsChild = array();

        if ($this->curTokenTypeIs('*')){
            $fieldsChild[] = $this->makeAst('*', '*');
            $this->nextToken();
            return $fieldsChild;
        }

        while ($this->curTokenTypeIs('var'))
        {
            $varLiteral = $this->curTokenLiteral();
//            $this->varTable[$varLiteral] = '';
            $fieldsChild[] = $this->makeAst('var', $varLiteral);
            $this->nextToken();

            if ($this->curTokenTypeIs(',')){
                $this->nextToken();
            }
        }

        return $fieldsChild;
    }

    private function parseFrom()
    {
        if (!$this->curTokenTypeIs('var'))
        {
            $this->throw_error_info(__FUNCTION__, 'var', json_encode($this->curToken));
        }

        $fromChild = array();
        $varLiteral = $this->curTokenLiteral();
        $fromChild[] = $this->makeAst('var', $varLiteral);
        $this->nextToken();
        return $this->makeAst('from', $fromChild);
    }

    private function parseWhere ()
    {
        if ( !$this->curTokenTypeIs('num') &&
            !$this->curTokenTypeIs('str') &&
            !$this->curTokenTypeIs('var') &&
            !$this->curTokenTypeIs('(') )
        {
            $this->throw_error_info(__FUNCTION__, 'num|str|var|(', json_encode($this->curToken));
        }

        $whereChild = array();
        $paraChild[] = $this->parseGeneralExpr();
        return $this->makeAst('where', $whereChild);
    }

//    private function parseWhereExp()
//    {
//
//    }

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

    //a*b+c*d
    private function parseExpr($precedence)
    {

//        $left = []

        if ($this->curTokenTypeIs('num'))
        {
//            $left = (int)$this->curTokenLiteral();
            $left = ['kind'=>'num', 'child'=> (int)$this->curTokenLiteral()];
            $this->nextToken();
        }
        elseif ($this->curTokenTypeIs('str'))
        {
            $left = ['kind'=>'str', 'child'=> $this->curTokenLiteral()];
            $this->nextToken();
        }
        elseif ($this->curTokenTypeIs('var'))
        {
            $left = ['kind'=>'var', 'child'=> $this->curTokenLiteral()];
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
            'or' => 1,
            'and' => 2,
            '>' => 3,
            '=' => 3,
            '<' => 3,
            '+' => 4,
            '-' => 4,
            '*' => 5,
            '/' => 5,
            '(' => 5,
            ')' => 5
        ];

        return $precedences[$this->curTokenType()] ?? 0;
    }
    private function IsOperator($tokenType)
    {
        $operators = array(
            '+', '-', '*', '/',
            '>', '=', '<',
            'and', 'or'
        );
        return in_array(strtolower($tokenType), $operators);
    }

    private function parseOrderBy()
    {
        if (!$this->curTokenTypeIs('var'))
        {
            $this->throw_error_info(__FUNCTION__, 'var', json_encode($this->curToken));
        }

        $orderChild = array();
//        $varLiteral = $this->curTokenLiteral();
//        $orderChild[] = $this->makeAst('var', $varLiteral);

        while ($this->curTokenTypeIs('var'))
        {
            $varLiteral = $this->curTokenLiteral();
            $orderChild['fields'][] = $this->makeAst('var', $varLiteral);
            $this->nextToken();

            if ($this->curTokenTypeIs(',')){
                $this->nextToken();
            }
        }

        if ($this->curTokenLiteral() == 'desc' || $this->curTokenLiteral() == 'asc'){
            $varLiteral = $this->curTokenLiteral();
            $orderChild[] = $this->makeAst('type', $varLiteral);
            $this->nextToken();
        }

//        $this->nextToken();
        return $this->makeAst('order_by', $orderChild);
    }

    // 当前token的 类型
    private function curTokenType()
    {
        return $this->curToken['type'];
    }

    private function curTokenLiteral()
    {
        return strtolower($this->curToken['literal']);
    }

    private function curTokenTypeIs($tokenType)
    {
        return $this->curToken['type'] == $tokenType;
    }

    private function nextTokenLiteral()
    {
        return strtolower($this->nextToken['literal']);
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

    private function throw_error_info($curFunc, $exceptType, $curType)
    {
//        throw new Exception($curFunc.' error, expect type is '.$exceptType.', but give type is '.$curType);
        var_dump($curFunc.' error, expect type is '.$exceptType.', but give type is '.$curType);
        exit();
    }
}