简介：从今天开始，我们用PHP实现一门新的语言，HW（hello world）语言，目的就是更好的理解一门脚本语言的运行机制。本篇内容就是介绍一下这门语言的四个部分，词法分析、语法分析、生成语法树、语法树解释器，并实现这门语言的最基本的两个功能，定义变量、输出变量。下面直接开始...

#### 一、准备工作

1. 首先我们准备如下目录结构：
```
├── app/            ----- 存放HW语言文件目录
│   └── hellow.hw    ----- HW语言文件
├── index.php         ----- 入口文件
├── lexer.php        ----- 词法分析文件
├── parser.php    ----- 语法分析文件
├── eval.php    ----- 语法树解析器文件
└── test.php  ----- 测试文件
```
2. 我们在`hello.hw`文件中写入待执行的代码
```
let aa = "hello world"  
echo aa
```
其中，第一行就是定义一个变量`aa`并赋值为`hello world`，第二行为变量的输出。

#### 二、测试

1. 说明：测试这一部分本可以归为准备工作，但是这种 **“先写测试，再写代码”** 的思想不仅可以用于本次新语言的开发，在平时的工作中也可以这样。
2. 测试文件的写法和思想其实很简单：“列出我们期望得到的结果和实际代码返回的结果，并比较两种结果是否相等即可”。
3. 下面开始编写测试代码：
3-1. lexer词法分析测试：
词法分析就是把要执行的HW的代码分割成一个个**“词”**，又或者叫**“token”**，本来`hello.hw`文件中的代码应该分割成：
```php
$exp_lexer = [ 'let', 'aa', '=', 'hello world', 'echo',  'aa'];
```
但是经过试验证明，这样不太好做，这里我们就绕过验证过程，有兴趣你可以自己去试试。我们的解决办法就是给每一个`token`加上类型，这样操作起来就容易的多了，就像下面这样:
```php
$exp_lexer = [
    ['type' => 'kw', 'literal' => 'let'],
    ['type' => 'var', 'literal' => 'aa'],
    ['type' => '=', 'literal' => '='],
    ['type' => 'str', 'literal' => 'hello world'],
    ['type' => 'kw', 'literal' => 'echo'],
    ['type' => 'var', 'literal' => 'aa']
];
```
其中，`kw`代表关键字（key word），`var`代表变量，`str`代表字符串，符号的类型就是它本身。目前只关心这四种，再有再加就可以了。那么词法分析期望返回的结果定义好了，再定义一个用于比较期望结果和实际结果的方法就可以了。
```php
//$input代表待分析的源代码，$expect代表期望返回的结果
function testLexer($input, $expect) {
    //此处为伪代码，代表调用词法分析方法，tokens存放实际返回结果
    $tokens = lexer($input);
    if ($tokens != $expect) {
        echo "expect token is:";
        echo json_encode($expect);
        echo "<br>";
        echo "givens token is:";
        echo json_encode($tokens);
        exit();
    }

    print "lexer test pass \n";
}
```
#### 整体思路：
>下面是最主要的三部分，分别为一个类`class`，这样使得代码比较清晰，而且减少代码的冗余。但是要注意的是lexer和parser这两部分是紧密联系在一起的，虽然代码分别在一个`class`中，但是使用的时候却是parser不停的从lexer中取值，即parser从lexer中获取一个个`token`，然后根据`token`的一个或者几个组合，分析语法，并生成相应的语法树，eval再解析语法树。（如果代码先经过lexer分析生成一个个`token`，然后再把所有的`token`在parser中进行分析。。。这无论从时间还是空间上都是一个巨大的开销，这样是不对的）

#### 三、lexer词法分析器

1. lexer 类的原理: 输入源码，解析成一个个`token`。根据整体思路来说，我们只需要对外提供一个公共方法`nextToken()`即可，用来返回一个`token`给parser。  
2. 开始编码：首先，定义几个类属性和常量，用于存储特殊值，具体含义见注释
```php
class Lexer
{
    private $input; // 输入的字符串

    private $pos = 0;  // 当前字符的位置

    private $char; // 当前的字符

    //关键字集合
    private $KeyWords = array(
        'let',
        'echo'
    );

    //文件结尾
    const EOF = -1;
}

```
同时，还需要一个构造方法，用于输入源码的存储，以及第一个字符的赋值
```php
public function __construct(string $input)
{
    $this->input = $input;
    $this->char = $this->input[$this->pos];
}
```
3. 然后，定义公共方法`nextToken()`，目前来说方法的具体实现我们还没有思路，但是我们知道肯定是返回一个token，而且token有类型，有具体的值，那么我们就先写个最简单的出来：
```php
//主方法，获取下一个token的值
public function nextToken(): array
{
    return $this->makeToken($this->char, $this->char);
}

//生成token
private function makeToken($type, $literal): array
{
    //其中type为token的类型，literal为token的值
    return ['type' => $type, 'literal' => $literal];
}
```
4. 继续思考，我们先不考虑复杂的源码匹配，就现在的源码而言，每一行的第一个单词，（也是我们要匹配的第一个token）都为关键字，所以我们先写出匹配关键字的方法：
```php
//匹配关键字
private function matchKw()
{
    return $KeyWord;
}
```
想要匹配关键字，首先需要对关键字进行分析，得出关键字的两个条件：
>一、由英文字母组成；
>二、在我们前面定义的关键字数组中；

条件二好判断，PHP的`in_array()`方法即可。
条件一需要当某个位置的字符为英文字母，并且后面连接几个字符都为英文字符时才满足。判断一个字符是否为英文字母，可以用`==`判断，但是这种需要用到循环判断，很明显不是一个好办法，这里我们用的是判断字符的ASCII码值的方法，看该字符的ASCII码值是否大于等于字符a的ASCII码值，并且小于等于z的ASCII码值：
```php
//判断字符是否为英文字母a~z
private function isLetter()
{
    $ord = ord($this->char);

    if ($ord >= 97 && $ord <= 122)//a~z
    {
        return true;
    }

    return false;
}
```
以上是判断单个字符是否为英文字符，但是匹配英文字符串还需要不停的读取并判断下一个字符，直到遇到不是英文字母的字符为止，于是：
```php
//匹配单词
private function matchWord(): string
{
    $word = '';

    while ($this->isLetter())
    {
        $word .= $this->char;
        $this->readChar();
    }

    return $word;
}

//因为读取下一个字符会有很多地方用到，所以抽象为一个方法

//读取下一个字符
private function readChar()
{
    $this->char = $this->input[$this->pos++] ?? self::EOF;
}
```
判断是否为关键字：
```php
//判断是否为关键字
private function isKw($str)
{
    return in_array($str, $this->KeyWords);
}
```
好了，现在我们可以匹配关键字了，但是我们什么时候去匹配关键字呢，条件和时机是什么？答案是在获取token时，也就是在`nextToken()`方法中，当我们遇到一个字符为英文字母时：
```php
public function nextToken(): array
{
    if ($this->isLetter()) //是否为英文字符
    {
        $word = $this->matchWord();
        if ($this->isKw($word))  //是否为关键字
        {
            return $this->makeToken('kw', $word);
        }
        else 
        {   //否则直接返回匹配内容
            return $this->makeToken($word, $word);
        }
    }
    elseif ($this->char == self::EOF)
    {
        return $this->makeToken('eof', 'EOF');
    }

    var_dump('unknown char：' . $this->char);
    return $this->makeToken('eof', 'EOF');
}
```
此时，我们修改test.php文件，开始调试我们写好的代码，看能否匹配到关键字
```php
$json = file_get_contents("hw/hello.hw");
testLexer($json, $exp_lexer);
function testLexer($input, $expect) {
    $lexer = new Lexer($input);
    $tokens = [];

    while (($tok = $lexer->nextToken())['type'] != 'eof') {
        $tokens[] = $tok;
    }
    ...
}
```
切换到test.php文件所在目录，命令行运行 `php test.php`
```php
array(2) {
  ["type"]=>
  string(2) "kw"
  ["literal"]=>
  string(3) "let"
}
string(16) "unknown char： "
expect token is:[{"type":"kw","literal":"let"},{"type":"var","literal":"aa"}...
```
由以上输出可以看到，关键字`let`成功匹配并返回，但是接下来的字符空格还未做处理，这里我们可以在主方法`nextToken()`中添加else分支，但是细想，如果有多个空格连续呢，并且还有其他特殊字符，比如回车。。。所以，我们抽象出一个方法，用于跳过这些对我们无意义的字符
```php
//跳过空白符
private function skipBlank()
{
    while ( ord($this->char) == 10 || //换行
            ord($this->char) == 13 || //回车
            ord($this->char) == 32 )  //空格
    {
        $this->readChar();
    }
}
```
方法有了，但是我们把它加到哪里呢？第一个地方，每次匹配完成，返回`token`之前；如果这样，就会发现每个匹配的判断里都要加上这个方法；第二，放到`nextToken()`的最开始，这样，每次匹配只关注对应的匹配内容，其他无意义的字符，下次匹配之前就自动略过了。对比发现，还是第二种是最合适的。
于是，就有了下面的代码：

```php
//主方法，获取下一个token的值
public function nextToken(): array
{
    //跳过空白符
    $this->skipBlank();
    ......
}
```
再次运行`test.php`
```php
array(2) {
  ["type"]=>
  string(2) "kw"
  ["literal"]=>
  string(3) "let"
}
array(2) {
  ["type"]=>
  string(2) "aa"
  ["literal"]=>
  string(2) "aa"
}
string(16) "unknown char：="
expect token is:[{"type":"kw","literal":"let"},  ......
```
以上内容我们可以看出，第一，变量`aa`没有还有处理为对应的`token`类型；第二，`=`等号没有做相应的匹配。第二个问题相对容易解决，因为符号种类有限，而且我们不需要对符号加特殊的`token`类型，所以，直接抽象出一个匹配符号的方法即可：
```php
private function isSymbol($c='')
{
    $c = $c?:$this->c;
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
        $c == '{' ||
        $c == '}' 
    )
    {
        return true;
    }

    return false;
}

//然后完善nextToken()，加上对应的分支
elseif ($this->isSymbol()) {
    $symbol = $this->char;
    $token = $this->makeToken($symbol, $symbol);
    $this->readChar();
    return $token;
}
```
回到第一个问题，首先我们要明确变量的命名规则，由 `_、0~9、a~z、A~Z` 组成，这其中包含了关键字的组成部分 `a~z` ，于是我们可以把两部分合并，并抽象一个方法，匹配相应的内容，然后再判断是否为关键字，不是关键字的都归为变量：
```php
//判断是否为变量字符
private function isVarChar($c='')
{
    $c = $c?:$this->char;
    $ord = ord($c);
    if ( $ord == 95 || //_
        ($ord >= 48 && $ord <=57) || //0~9
        ($ord >= 65 && $ord <= 90) || //A~Z
        ($ord >= 97 && $ord <= 122) )  //a~z
    {
        return true;
    }

    return false;
}

//nextToken()分支
if ($this->isVarChar()) //是否为变量字符
{
    $word = $this->matchWord();
    if ($this->isKw($word))  //是否为关键字
    {
        return $this->makeToken('kw', $word);
    }
    else
    {   //否则直接返回匹配内容
        return $this->makeToken('var', $word);
    }
}
```
再次运行 `test.php` 
```php
array(2) {
  ["type"]=>
  string(2) "kw"
  ["literal"]=>
  string(3) "let"
}
array(2) {
  ["type"]=>
  string(3) "var"
  ["literal"]=>
  string(2) "aa"
}
array(2) {
  ["type"]=>
  string(1) "="
  ["literal"]=>
  string(1) "="
}

string(16) "unknown char：""
......
```
没问题，前面的部分都已经完美匹配。

下一个未匹配的字符为 `"` 引号，这里我们不把它跟 `=` 归为符号类，而是当我们遇到引号时，接下来的一系列字符为一个字符串整体，直到遇到另一个引号结束，于是我们就跟签名一样，抽象出匹配字符串方法，并且添加 `nextToken()` 的匹配字符串分支：
```php
......
elseif ($this->char == '"') //""中的内容视为一个整体，字符串
{
    $this->readChar();
    $str = $this->matchStr();
    $token = $this->makeToken('str', $str);
    $this->readChar();
    return $token;
}
......

//匹配字符串
private function matchStr(): string
{
    $str = '';
    while ($this->char != '"' && $this->char != self::EOF) {
        $str .= $this->char;
        $this->readChar();
    }
    return $str;
}
```

再次运行 `test.php` 发现 `lexer test pass ` ，说明我们的词法分析器已经能正常工作，并且成功解析HW源码为我们想要的格式。虽然这部分代码还不完善，但是我们已经学到了词法分析的一种新思路，后面我们要做的就是不断的完善它就可以了。

#### 四、parser语法分析器
#### 五、eval