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
//                return array();
            case 'kw':
                $this->parseKw();
//                return $this->ast;
                return array();
            default:
                p($this->curToken);
                p($this->nextToken);
                $this->throw_error('不知道啥情况', 'parse');
        }
    }

    private function parseKw()
    {
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

        if ( ! $this->curTokenTypeIs('str') )
        {
            $this->throw_error_info(__FUNCTION__, 'str', json_encode($this->curToken));
        }

        $this->varTable[$varLiteral] = $this->curTokenLiteral();
        $assignChild[] = $this->makeAst('str', $this->curTokenLiteral());

        $this->ast['child'][] = $this->makeAst('assign', $assignChild);

        $this->nextToken();
    }

    private function parseEcho()
    {
        $echoChild = array();

        if ($this->curTokenTypeIs('str'))
        {
//            echo $this->curTokenLiteral();

            $echoChild = $this->makeAst('str', $this->curTokenLiteral());
            $this->ast['child'][] = $this->makeAst('echo', $echoChild);

            $this->nextToken();
            return;
        }
        elseif ($this->curTokenTypeIs('var'))
        {
            if ( ! isset($this->varTable[$this->curTokenLiteral()]) )
            {
                $this->throw_error('var '.$this->curTokenLiteral().' is undefined');
            }

//            echo $this->varTable[$this->curTokenLiteral()];

            $echoChild = $this->makeAst('var', $this->curTokenLiteral());
            $this->ast['child'][] = $this->makeAst('echo', $echoChild);

            $this->nextToken();
            return;
        }
    }

    public function parseExpr($precedence)
    {
        if (is_numeric($this->token)) {
            $left = (int)$this->token;
            $this->nextToken();
        } else {
            $left = $this->parseGroup();
        }

        while ($this->token !== null && $precedence < $this->curPrecedence()) {
            $left = $this->parseInfixExpr($left);
        }

        return $left;
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