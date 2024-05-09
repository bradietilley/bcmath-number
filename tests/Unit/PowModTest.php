<?php

use BradieTilley\BCMath\Number;

test('Number can powmod', function () {
    $num = new Number('4');
    $exponent = new Number('5');
    $modulus = new Number('3');

    $result = $num->powmod($exponent, $modulus);
    expect($result->value)->toBe('1');
    // value is '1', The scale is always 0 because the result is always an integer.
});
