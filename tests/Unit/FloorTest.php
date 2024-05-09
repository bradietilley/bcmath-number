<?php

use BradieTilley\BCMath\Number;

test('Number can floor', function () {
    $num = new Number('1.23');
    $result = $num->floor();

    expect($result->value)->toBe('1');
});
