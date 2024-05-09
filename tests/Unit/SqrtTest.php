<?php

use BradieTilley\BCMath\Number;

test('Number can sqrt', function () {
    $num = new Number('1.23');
    $result = $num->sqrt();

    expect($result->value)->toBe('1.109053650641');
    // value is '1.109053650641', The max scale is the sum of the $num scale and maximum expansion scale. (2 + 10 = 12)

    $num = new Number('16.00');
    $result = $num->sqrt();

    expect($result->value)->toBe('4.00');
    // value is '4.00', The result fits within the maximum scale, so an implicit scale of 2 is set.
});
