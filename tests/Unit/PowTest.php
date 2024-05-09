<?php

use BradieTilley\BCMath\Number;

test('Number can pow', function () {
    $num = new Number('1.23');
    $exponent = new Number('3');
    $result = $num->pow($exponent, 0);

    expect($result->value)->toBe('1.860867');
    // value is '1.860867', The value of the left operand scale multiplied by exponent becomes the resulting scale. (2 * 3 = 6)

    $num = new Number('1.23');
    $exponent = new Number('0');
    $result = $num->pow($exponent, 0);

    expect($result->value)->toBe('0');
    // Scale is always 0 because the 0th power is always 1.

    $num = new Number('1.23');
    $exponent = new Number('-3');
    $result = $num->pow($exponent, 0);

    expect($result->value)->toBe('0.537383918356');
    // value is '0.537383918356', The maximum scale is the sum of the left operand's scale and maximum expansion scale. (2 + 10 = 12)
});
