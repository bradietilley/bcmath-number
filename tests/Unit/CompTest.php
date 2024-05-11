<?php

use BradieTilley\BCMath\Number;

test('Number can comp', function () {
    $num = new Number('1.23');
    $num2 = new Number('1.23456');

    $result = $num->comp($num2);
    expect($result)->toBe(-1);
    // -1, Same as '1.23' <=> '1.23456'

    $result = $num->comp($num2, 2);
    expect($result)->toBe(0);
    // 0, Same as '1.23' <=> '1.23'


    $result = $num->eq($num2);
    expect($result)->toBe(false);
    // false, Same as '1.23' == '1.23456'

    $result = $num->eq($num2, 2);
    expect($result)->toBe(true);
    // true, Same as '1.23' == '1.23'


    $result = $num->gt($num2);
    expect($result)->toBe(false);
    // false, Same as '1.23' > '1.23456'

    $result = $num->gt($num2, 2);
    expect($result)->toBe(false);
    // false, Same as '1.23' > '1.23'


    $result = $num->gte($num2);
    expect($result)->toBe(false);
    // false, Same as '1.23' >= '1.23456'

    $result = $num->gte($num2, 2);
    expect($result)->toBe(true);
    // true, Same as '1.23' >= '1.23'


    $result = $num->lt($num2);
    expect($result)->toBe(true);
    // true, Same as '1.23' < '1.23456'

    $result = $num->lt($num2, 2);
    expect($result)->toBe(false);
    // false, Same as '1.23' < '1.23'


    $result = $num->lte($num2);
    expect($result)->toBe(true);
    // true, Same as '1.23' <= '1.23456'

    $result = $num->lte($num2, 2);
    expect($result)->toBe(true);
    // true, Same as '1.23' <= '1.23'
});

test('Number can comp with high precision on one side', function () {
    $num = new Number(0);
    $result = $num->gt('-0.000000000000001');
    expect($result)->toBe(true);
    $num = new Number('-0.000000000000001');
    $result = $num->lt(0);
    expect($result)->toBe(true);
});
