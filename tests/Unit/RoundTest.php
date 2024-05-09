<?php

use BradieTilley\BCMath\Number;

test('Number can round', function () {
    $num = new Number('1.23');
    $result = $num->round(1); // value is '1.2', Implicitly sets the scale from the rounded value.

    expect($result->value)->toBe('1.2');
});
