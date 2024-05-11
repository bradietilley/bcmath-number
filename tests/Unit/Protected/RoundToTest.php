<?php

use Tests\Fixtures\TestingNumber;

test('Number can round to', function (string|int $input, int $scale, int $roundingMode, ?string $decimal, ?string $thousands, string $expect, string $formatted = null) {
    $result = TestingNumber::roundTo($input, $scale, $roundingMode);
    expect($result)->toBe($expect);

    $result = TestingNumber::rounded($input, $scale, $roundingMode);
    expect($result->value)->toBe($expect);
})->with('rounding');
