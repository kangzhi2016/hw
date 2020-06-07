<?php

class Lexer
{
    private $input; // 输入的字符串

    private $pos = 0;  // point to c char

    private $readPos = 0; // point to next char

    private $c; // current char

    private $KeyWords = array(
        'let',
        'echo',
        'func'
    );

    const EOF = -1;

    public function __construct(string $input)
    {
        $this->input = $input;
        $this->readChar();
    }

    public function nextToken(): array
    {
        $this->skipBlank();

        if ($this->isSymbol()) {
            $token = $this->makeToken($this->c, $this->c);
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
                return $this->makeToken('kw', $str);
            } else {
                return $this->makeToken('var', $str);
            }
        }
        elseif ($this->c == '"')
        {
            $this->readChar();
            $str = $this->matchStr();
            $token = $this->makeToken('str', $str);
            $this->readChar();
            return $token;
        }
        elseif ($this->c == self::EOF)
        {
            return $this->makeToken('eof', 'EOF');
        }

        throw new Exception(__FUNCTION__ . ' error, $this->c=' . $this->c);
    }

    private function matchStr(): string
    {
        $str = '';
        while ($this->c != '"' && $this->c != self::EOF) {
            $str .= $this->c;
            $this->readChar();
        }
        return $str;
    }

    private function matchNum(): string
    {
        $num = '';
        $ord = ord($this->c);
        while ($ord >= 48 && $ord <= 57) {
            $num .= $this->c;
            $this->readChar();
            $ord = ord($this->c);
        }
        return $num;
    }

    private function matchVarName($str=''): string
    {
        $str = $str?:'';

        while ($this->isVarName())
        {
            $str .= $this->c;
            $this->readChar();
        }

        return $str;
    }

    private function isKw($str)
    {
        return in_array($str, $this->KeyWords);
    }

    private function isChar($c='')
    {
        $c = $c?:$this->c;
        $ord = ord($c);
        //a~z || A~Z
        return (($ord >= 65 && $ord <= 90) || ($ord >= 97 && $ord <= 122))?true:false;
    }

    private function isNumber($c='')
    {
        $c = $c?:$this->c;
        $ord = ord($c);
        return ($ord >= 48 && $ord <= 57)?true:false;
    }

    private function isSymbol($c='')
    {
        $c = $c?:$this->c;
        if ($c == '=' ||
            $c == '+' ||
            $c == '-' ||
            $c == '*' ||
            $c == '/' ||
            $c == '(' ||
            $c == ')' ||
            $c == '{' ||
            $c == '}')
        {
            return true;
        }

        return false;
    }

    private function isVarName($c='')
    {
        $c = $c?:$this->c;
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
        $this->pos = $this->readPos;
        $this->c = $this->input[$this->readPos] ?? self::EOF;
        $this->readPos++;
    }

    private function skipBlank()
    {
        while( $this->c == " " || $this->c == "\t" || $this->c == "\n" || $this->c == "\r" || $this->c == "\r\n" || $this->c == "\n\r")
        {
            $this->readChar();
        }
    }

    private function makeToken($type, $literal): array
    {
        return ['type' => $type, 'literal' => $literal];
    }
}