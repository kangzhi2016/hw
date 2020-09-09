<?php

class Lexer
{
    private $input; // 输入的字符串

    private $pos = 0;  // point to c char

    private $char; // current char

    const EOF = -1;

    private $KeyWords = array(
        'select',
        'from',
        'where',
        'or',
        'and',
        'order',
        'by',
        'asc',
        'desc',
        'limit'
    );

    public function __construct(string $input)
    {
        $this->input = $input;
        $this->char = $this->input[$this->pos];
    }

    public function nextToken(): array
    {
        $this->skipBlank();

        if ($this->isSymbol())
        {
//            $symbol = $this->matchSymbol();
//            $token = $this->makeToken($symbol, $symbol);
            $token = $this->makeToken($this->char, $this->char);
            $this->readChar();
            return $token;
        }
        elseif ($this->isNumber())
        {
            $num = $this->matchNum();
            return $this->makeToken('num', $num);
        }
        elseif ($this->isVarName())
        {
            $str = $this->matchVarName();
            if ($this->isKw($str)) {
                return $this->makeToken($str, $str);
            } else {
                return $this->makeToken('var', $str);
            }
        }
        elseif ($this->char == '"' || $this->char == "'" )
        {
            $this->readChar();
            $str = $this->matchStr();
            $token = $this->makeToken('str', $str);
            $this->readChar();
            return $token;
        }
        elseif ($this->char == self::EOF)
        {
            return $this->makeToken('eof', 'EOF');
        }

//        throw new Exception(__FUNCTION__ . ' error, $this->char=' . $this->char);
        pt(__FUNCTION__ . ' error, $this->char=' . $this->char);
    }

    private function matchStr(): string
    {
        $str = '';
        while ($this->char != '"' && $this->char != self::EOF) {
            $str .= $this->char;
            $this->readChar();
        }
        return $str;
    }

    private function matchNum(): string
    {
        $num = '';
        $ord = ord($this->char);
        while ($ord >= 48 && $ord <= 57) {
            $num .= $this->char;
            $this->readChar();
            $ord = ord($this->char);
        }
        return $num;
    }

    private function matchVarName($str=''): string
    {
        $str = $str?:'';

        while ($this->isVarName())
        {
            $str .= $this->char;
            $this->readChar();
        }

        return $str;
    }

    private function matchSymbol(): string
    {
        $symbol = '';

        while ($this->isSymbol())
        {
            $symbol .= $this->char;
            $this->readChar();
        }

        return $symbol;
    }

    private function isKw($str)
    {
        return in_array(strtolower($str), $this->KeyWords);
    }

    private function isChar($c='')
    {
        $c = $c?:$this->char;
        $ord = ord($c);
        //a~z || A~Z
        return (($ord >= 65 && $ord <= 90) || ($ord >= 97 && $ord <= 122))?true:false;
    }

    private function isNumber($c='')
    {
        $c = $c?:$this->char;
        $ord = ord($c);
        return ($ord >= 48 && $ord <= 57)?true:false;
    }

    private function isSymbol($c='')
    {
        $c = $c?:$this->char;
        if ($c == '=' ||
            $c == '+' ||
            $c == '-' ||
            $c == '*' ||
            $c == '/' ||
            $c == '>' ||
            $c == '<' ||
            $c == '!' ||
            $c == '(' ||
            $c == ')' ||
            $c == ',' ||
            $c == '`'
            )
        {
            return true;
        }

        return false;
    }

    private function isVarName($c='')
    {
        $c = $c?:$this->char;
        $ord = ord($c);
        if ( $ord == 95 || //_
            ($ord >= 48 && $ord <=57) || //0~9
            ($ord >= 65 && $ord <= 90) || //a~z
            ($ord >= 97 && $ord <= 122) )  //A~Z
        {
            return true;
        }

        return false;
    }

    private function readChar()
    {
//        $this->pos = $this->readPos;
//        $this->char = $this->input[$this->readPos] ?? self::EOF;
//        $this->readPos++;

        $this->char = $this->input[++$this->pos] ?? self::EOF;
    }

    private function skipBlank()
    {
        while( $this->char == " " || $this->char == "\t" || $this->char == "\n" || $this->char == "\r" || $this->char == "\r\n" || $this->char == "\n\r")
        {
            $this->readChar();
        }
    }

    private function makeToken($type, $literal): array
    {
        return ['type' => $type, 'literal' => $literal];
    }
}

