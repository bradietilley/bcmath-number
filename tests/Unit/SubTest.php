<?php

use BradieTilley\BCMath\Number;

test('Number can sub integers', function () {
    $num = new Number('9');
    $num2 = new Number('3');

    expect($num->sub($num2)->value)->toBe('6');
});

test('Number can add and use higher scale of two', function () {
    $num = new Number('1.23');
    $num2 = new Number('2.000000');

    expect($num->sub($num2)->value)->toBe('-0.770000');
    // value is '-0.770000', The larger scale of the two values is applied. (2 < 6, so 6 is used)
});
