<?php

class Parser
{
    private $lexer;

    // 当前token
    private $curToken;

    // 下一个token
    private $nextToken;

    //语法树
    private $ast = array();

    public function __construct(Lexer $lexer)
    {
        $this->lexer = $lexer;
        $this->curToken = $this->lexer->nextToken();
        $this->nextToken = $this->lexer->nextToken();
    }

    // 主方法
    public function parse()
    {
        $astRoot = array();

        $this->expectCurTokenType('select');
        $astRoot[] = $this->parseSelect();

        $this->expectCurTokenType('from');
        $astRoot[] = $this->parseFrom();

        if ($this->curTokenTypeIs('where')){
            $this->expectCurTokenType('where');
            $astRoot[] = $this->parseWhere();
        }else{
            $astRoot[] = array();
        }

        if ($this->curTokenTypeIs('order')){
            $this->expectCurTokenType('order');
            $this->expectCurTokenType('by');
            $astRoot[] = $this->parseOrderBy();
        }else{
            $astRoot[] = array();
        }

        if ($this->curTokenTypeIs('limit')){
            $this->expectCurTokenType('limit');
            $astRoot[] = $this->parseLimit();
        }

        $this->ast = $this->makeAst('root', $astRoot);
        return $this->ast;
    }

    private function parseSelect()
    {
        if (!$this->curTokenTypeIs('var') && !$this->curTokenTypeIs('*')) {
            $this->throw_error_info(__FUNCTION__, 'var|*', json_encode($this->curToken));
        }

        return $this->makeAst('select', $this->parseSelectFields(), 'select');
    }

    //select *,id
    private function parseSelectFields()
    {
        $fieldsChild = array();

        if ($this->curTokenTypeIs('*')){
            $fieldsChild[] = $this->makeAst('*', '*');
            $this->nextToken();
        }elseif ($this->curTokenTypeIs('var')){
            $fieldsChild[] = $this->parseVar();
        }

        while ($this->curTokenTypeIs(',')) {
            $this->nextToken();
            $fieldsChild[] = $this->parseVar();
        }

        return $fieldsChild;
    }

    private function parseVar()
    {
        $varLiteral = $this->curTokenLiteral();
        $varAst = $this->makeAst('var', $varLiteral);
        $this->expectCurTokenType('var');

        return $varAst;
    }

    private function parseFrom()
    {
        return $this->makeAst('from', $this->parseVar(), 'from');
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

        $whereChild = $this->parseExpr(0);
        return $this->makeAst('where', $whereChild, 'where');
    }

    //a*b+c*d
    private function parseExpr($precedence)
    {
        if ($this->curTokenTypeIs('num')) {
            $left = ['kind'=>'num', 'child'=> (int)$this->curTokenLiteral()];
            $this->nextToken();
        } elseif ($this->curTokenTypeIs('str')) {
            $left = ['kind'=>'str', 'child'=> $this->curTokenLiteral()];
            $this->nextToken();
        } elseif ($this->curTokenTypeIs('var')) {
            $left = ['kind'=>'var', 'child'=> $this->curTokenLiteral()];
            $this->nextToken();
        } elseif ($this->curTokenTypeIs('(')) {
            $left = $this->parseExpGrop();
        }

        while ( !$this->curTokenTypeIs('eof') && $precedence < $this->curPrecedence()) {
            $left = $this->parseInfixExpr($left);
        }

        return $left;
    }
    private function parseInfixExpr($left)
    {
        $op = $this->curTokenLiteral();
        $precedence = $this->curPrecedence();
        $this->nextToken();
        $expAst[] = $left;
        $expAst[] = $this->parseExpr($precedence);

        return $this->makeAst('exp', $expAst, $op);
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
            '/' => 5
        ];

        return $precedences[$this->curTokenType()] ?? 0;
    }

    private function parseExpGrop()
    {
        $this->nextToken();//(
        $left = $this->parseExpr(0);
        $this->nextToken(); //)
        return $left;
    }

    //order by id,price desc
    //order by id desc,price asc
    private function parseOrderBy()
    {
        $orderChild = array();
        $orderChild[] = $this->parseOrderByGroup();

        while ($this->curTokenTypeIs(',')) {
            $this->nextToken();
            $orderChild[] = $this->parseOrderByGroup();
        }

        return $this->makeAst('order_by', $orderChild, 'order_by');
    }

    private function parseOrderByGroup(){
        $var = $this->parseVar();
        $type = 'asc';
        if ($this->curTokenTypeIs('asc') || $this->curTokenTypeIs('desc')){
            $type = $this->curTokenLiteral();
            $this->nextToken();
        }
        return $this->makeAst('order_by_group', $var, $type);
    }

    private function parseLimit()
    {
        $limitChild = array();
        $limitChild[] = $this->parseNum();

        if ($this->curTokenTypeIs(',')){
            $this->nextToken();
            $limitChild[] = $this->parseNum();
        }

        return $this->makeAst('limit', $limitChild, 'limit');
    }

    private function parseNum()
    {
        $numLiteral = $this->curTokenLiteral();
        $numAst = $this->makeAst('num', $numLiteral);
        $this->expectCurTokenType('num');
        return $numAst;
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
    }

    private function makeAst($kind, $child, $attr='')
    {
        if (!$attr){
            return ['kind' => $kind, 'child' => $child];
        }else{
            return ['kind' => $kind, 'attr' => $attr, 'child' => $child];
        }
    }

    private function throw_error_info($curFunc, $exceptType, $curType)
    {
        var_dump($curFunc.' error, expect type is '.$exceptType.', but give type is '.$curType);
        exit();
    }
}