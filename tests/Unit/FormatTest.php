<?php

use BradieTilley\BCMath\Number;

test('Number can format', function (string|int $input, int $scale, int $roundingMode, ?string $decimal, ?string $thousands, string $fullNumber, string $expect = null) {
    $num = new Number($input);
    $result = $num->format($scale, $roundingMode, $decimal ?? '.', $thousands ?? '');
    $expect ??= $fullNumber;

    expect($result)->toBe($expect);
})->with('rounding');
