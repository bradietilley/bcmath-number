<?php

use BradieTilley\BCMath\Number;

test('Number has automatic scale on construct', function () {
    $num = new Number('2');
    $result = $num->scale;
    expect($result)->toBe(0);
    // value is '2', scale is 0

    $num = new Number('0.12345');
    $result = $num->scale;
    expect($result)->toBe(5);
    // value is '0.12345', scale is 5

    $num = new Number('2.0000');
    $result = $num->scale;
    expect($result)->toBe(4);
    // value is '2.0000', scale is 4
});
