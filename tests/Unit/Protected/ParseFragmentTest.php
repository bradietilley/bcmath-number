<?php

use Tests\Fixtures\TestingNumber;

test('Number can parse fragments', function (string $input, string $wholeNumber, string $decimalNumber) {
    $result = TestingNumber::parseFragments($input);

    expect($result)->toBe([
        $wholeNumber,
        $decimalNumber,
    ]);
})->with([
    [ '12', '12', ''],
    [ '12.34', '12', '34'],
    [ '435435.345346345', '435435', '345346345'],
    [ '9.8', '9', '8'],
    [ '-32423.0', '-32423', '0'],
    [ '938457475983475938475938.239921847392740236589345349', '938457475983475938475938', '239921847392740236589345349'],
    [ '0.0', '0', '0'],
]);
