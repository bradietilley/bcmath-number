<?php

use BradieTilley\BCMath\Number;

test('Number can div integers', function () {
    $num = new Number('16');
    $num2 = new Number('8');
    $result = $num->div($num2);

    expect($result->value)->toBe('2');
});

test('Number can div and use correct scale', function () {
    // maximum expansion scale is 10

    $num = new Number('1.23');
    $num2 = new Number('3.333');
    $result = $num->div($num2);

    expect($result->value)->toBe('0.369036903690');
    // value is '0.369036903690', The max scale is the sum of the dividend scale and maximum expansion scale. (2 + 10 = 12)

    $num = new Number('1.25');
    $num2 = new Number('5');
    $result = $num->div($num2);

    expect($result->value)->toBe('0.25');
    // value is '0.25', The result fits within the maximum scale, so an implicit scale of 2 is set.

    $num = new Number('1.25000');
    $num2 = new Number('5');
    $result = $num->div($num2);

    expect($result->value)->toBe('0.25000');
    // value is '0.25000', The result fits within the maximum scale, so an implicit scale of 5 is set.

    $num = new Number('1.25000');
    $num2 = new Number('5.00');
    $result = $num->div($num2);

    expect($result->value)->toBe('0.25000');
    // value is '0.25000', The result fits within the maximum scale, so an implicit scale of 5 is set.
});
