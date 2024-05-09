<?php

use BradieTilley\BCMath\Number;

test('Number can comp', function () {
    $num = new Number('1.23');
    $num2 = new Number('1.23456');

    expect($num->comp($num2))->toBe(-1);
    // -1, Same as '1.23' <=> '1.23456'
    expect($num->comp($num2, 2))->toBe(0);
    // 0, Same as '1.23' <=> '1.23'

    expect($num->eq($num2))->toBe(false);
    // false, Same as '1.23' == '1.23456'
    expect($num->eq($num2, 2))->toBe(true);
    // true, Same as '1.23' == '1.23'

    expect($num->gt($num2))->toBe(false);
    // false, Same as '1.23' > '1.23456'
    expect($num->gt($num2, 2))->toBe(false);
    // false, Same as '1.23' > '1.23'

    expect($num->gte($num2))->toBe(false);
    // false, Same as '1.23' >= '1.23456'
    expect($num->gte($num2, 2))->toBe(true);
    // true, Same as '1.23' >= '1.23'

    expect($num->lt($num2))->toBe(true);
    // true, Same as '1.23' < '1.23456'
    expect($num->lt($num2, 2))->toBe(false);
    // false, Same as '1.23' < '1.23'

    expect($num->lte($num2))->toBe(true);
    // true, Same as '1.23' <= '1.23456'
    expect($num->lte($num2, 2))->toBe(true);
    // true, Same as '1.23' <= '1.23'
});
