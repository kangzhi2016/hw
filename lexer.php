<?php

class Lexer
{
    private $input; // 输入的字符串

    private $pos = 0;  // point to c char

    private $readPos = 0; // point to next char

    private $c; // current char

    private $KeyWords = array(
        'let',
        'echo'
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

        $ord = ord($this->c);

        if ($ord == 61) //=
        {
            $this->readChar();
            return $this->makeToken('=', '=');
        }
        elseif ($ord == 95 || //_
               ($ord >= 48 && $ord <=57) || //0~9
               ($ord >= 65 && $ord <=90) || //a~z
               ($ord >= 97 && $ord <=122))  //A~Z
        {
            $str = $this->matchKw();
            if ($this->isKw($str))
            {
                return $this->makeToken('kw', $str);
            }
            else
            {
                return $this->makeToken('var', $str);
            }
        }
        elseif ($this->c == '"') //"
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

        throw new Exception(__FUNCTION__.' error, $this->c='.$this->c);
    }

    private function matchStr(): string
    {
        $str = '';
        while( $this->c != '"' && $this->c != self::EOF)
        {
            $str .= $this->c;
            $this->readChar();
        }
        return $str;
    }

    private function matchKw(): string
    {
        $str = '';
        while( $this->c != ' ' && $this->c != '=' && $this->c != self::EOF)
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


    // 期望拿到什么字符
//    private function expectChar($char)
//    {
//        if($this->c != $char)
//        {
//            return false;
//        }
//
//        return true;
//    }

    private function readChar()
    {
        $this->pos = $this->readPos;
        $this->c = $this->input[$this->readPos] ?? self::EOF;
        $this->readPos++;
    }

//    private function peekChar(): string
//    {
//        return $this->input[$this->pos];
//    }

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