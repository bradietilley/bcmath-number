<?php

use BradieTilley\BCMath\Number;

test('Number can add integers', function () {
    $num = new Number('5');
    $num2 = new Number('6');
    $result = $num->add($num2);

    expect($result->value)->toBe('11');
});

test('Number can add and use higher scale of two', function () {
    $num = new Number('1.23');
    $num2 = new Number('2.000000');
    $result = $num->add($num2);

    expect($result->value)->toBe('3.230000');
    // value is '3.230000', The larger scale of the two values is applied. (2 < 6, so 6 is used)
});
