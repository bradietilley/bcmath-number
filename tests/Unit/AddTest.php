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

test('Number can add using high precision', function () {
    $num = new Number('9999999.999999999999999999999998');
    $result = $num->add('0.000000000000000000000001');
    expect($result->value)->toBe('9999999.999999999999999999999999');

    $num = new Number('9999999.999999999999999999999998');
    $result = $num->add('0.000000000000000000000002');
    expect($result->value)->toBe('10000000.000000000000000000000000');
});
