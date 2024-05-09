<?php

use BradieTilley\BCMath\Number;

test('Number can mul integers', function () {
    $num = new Number('8');
    $num2 = new Number('4');

    expect($num->mul($num2)->value)->toBe('32');
});

test('Number can mul and use sum of scales', function () {
    $num = new Number('1.23');
    $num2 = new Number('2.456');

    expect($num->mul($num2)->value)->toBe('3.02088');
    // value is '3.02088', The resulting scale is the sum of the scales of the two values. (2 + 3 = 5)

    $num = new Number('1.25');
    $num2 = new Number('4.00');

    expect($num->mul($num2)->value)->toBe('5.0000');
    // value is '5.0000', The resulting scale is the sum of the scales of the two values. (2 + 2 = 4)
});
