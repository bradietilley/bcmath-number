<?php

use BradieTilley\BCMath\Number;

test('Number can format', function (string|int $input, int $scale, int $roundingMode, ?string $decimal, ?string $thousands, string $expect) {
    $num = new Number($input);
    $result = $num->format($scale, $roundingMode, $decimal ?? '.', $thousands ?? '');

    expect($result)->toBe($expect);
})->with([
    ['12.34', 2, PHP_ROUND_HALF_UP, null, null, '12.34' ],
    ['-12.34', 2, PHP_ROUND_HALF_UP, null, null, '-12.34' ],
    ['-12.349455', 5, PHP_ROUND_HALF_UP, null, null, '-12.34846' ],
    ['-12.349455', 5, PHP_ROUND_HALF_DOWN, null, null, '-12.34845' ],
])->todo();
