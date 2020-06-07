<?php
/**
 * @Author zhanghaomin@100tal.com
 * @Time 2020/5/16 12:18 上午
 */
include 'ExprParser.php';
$ep = new ExprParser();
// pratt parser
// ['left' => 1, 'right' => 2, 'op' => '=']
$tests = [
    ['expr' => ['1'], 'expected' => 1],
    ['expr' => ['1', '+', '2'], 'expected' => ['left' => 1, 'op' => '+', 'right' => 2]],
    [
        'expr' => ['1', '+', '2', '+', '3'],
        'expected' => [
            'left' => ['left' => 1, 'op' => '+', 'right' => 2],
            'op' => '+',
            'right' => 3
        ]
    ],
    [
        'expr' => ['1', '+', '2', '*', '3', '/', '4'],
        'expected' => [
            'left' => 1,
            'op' => '+',
            'right' => [
                'left' => ['left' => 2, 'op' => '*', 'right' => 3],
                'op' => '/',
                'right' => 4
            ]
        ]
    ]
];

$tests2 = [
    ['expr' => ['1'], 'expected' => 1],
    ['expr' => ['1', '+', '2'], 'expected' => '(1 + 2)'],
    [
        'expr' => ['1', '+', '2', '+', '3'],
        'expected' => '((1 + 2) + 3)'
    ],
    [
        'expr' => ['1', '+', '2', '*', '3', '/', '4'],
        'expected' => '(1 + ((2 * 3) / 4))'
    ],
    [
        'expr' => ['(', '1', '+', '2', ')', '*', '3', '/', '4'],
        'expected' => '(((1 + 2) * 3) / 4)'
    ],
    [
        'expr' => ['(', '1', '/', '(', '5', '+', '6', ')', '*', '2', ')', '*', '3', '/', '4'],
        'expected' => '((((1 / (5 + 6)) * 2) * 3) / 4)'
    ]
];

foreach ($tests as  $k => ['expr' => $expr, 'expected' => $expected]) {
    if (($actual = $ep->parse($expr)) !== $expected) {
        if (is_array($actual)) {
            $actual = json_encode($actual);
        }

        if (is_array($expected)) {
            $expected = json_encode($expected);
        }

        print "expected $expected but given $actual on test $k\n";
        exit();
    }
}

foreach ($tests2 as  ['expr' => $expr, 'expected' => $expected]) {
    $actual = $ep->parse($expr);
    $actual = $ep->ast2Literal($actual);
    if ($actual !== $expected) {
        print "expected $expected but given $actual\n";
    }
}

print "test pass";