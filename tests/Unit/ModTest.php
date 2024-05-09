<?php

use BradieTilley\BCMath\Number;

test('Number can mod integers', function () {
    $num = new Number(10);
    $num2 = new Number(4);

    expect($num->mod($num2)->value)->toBe('2');
});

test('Number can mod and use the larger scale', function () {
    $num = new Number('6.234');
    $num2 = new Number('1.23');

    expect($num->mod($num2)->value)->toBe('0.084');
    // value is '0.084', the larger scale of the two values is applied. (3 > 2, so 3 is used)
});
