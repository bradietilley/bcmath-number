<?php

use BradieTilley\BCMath\Number;

test('Number can ceil', function () {
    $num = new Number('1.23');
    $result = $num->ceil();

    expect($result->value)->toBe('2');
});
