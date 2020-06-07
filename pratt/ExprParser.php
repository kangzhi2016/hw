<?php
/**
 * @Author zhanghaomin@100tal.com
 * @Time 2020/5/16 12:17 上午
 */

class ExprParser
{
    private $pos = -1;

    private $input = [];

    private $token = '';

    public function ast2Literal($ast)
    {
        if (is_array($ast)) {
            return '('.$this->ast2Literal($ast['left']).' '.$ast['op'].' '.$this->ast2Literal($ast['right']).')';
        }

        return (int)$ast;
    }

    public function parse(array $input)
    {
        $this->pos = -1;
        $this->input = $input;
        $this->nextToken();
        return $this->parseExpr(0);
    }

    private function parseInfixExpr($left)
    {
        $op = $this->token;
        $precedence = $this->curPrecedence();
        $this->nextToken();
        return ['left' => $left, 'op' => $op, 'right' => $this->parseExpr($precedence)];
    }

    private function parseGroup()
    {
        $this->nextToken(); // skip (
        $expr = $this->parseExpr(0);
        $this->nextToken(); // skip )
        return $expr;
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

    private function curPrecedence()
    {
        $precedences = [
            '+' => 1,
            '-' => 1,
            '*' => 2,
            '/' => 2
        ];

        return $precedences[$this->token] ?? 0;
    }

    private function nextToken()
    {
        $this->token = $this->input[++$this->pos] ?? null;
    }
}