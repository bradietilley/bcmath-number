<?php

declare(strict_types=1);

use BCMath\Number;

it('adds the class alias', function () {
    expect(Number::class)->toBeClass();
});

it('can use the class alias', function () {
    expect(new Number('1'))
        ->toBeInstanceOf(Number::class)
        ->eq(1)->toBeTrue();
});
