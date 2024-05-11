<?php

use Tests\Fixtures\TestingNumber;

test('Number can determine the scale of a decimal string', function (string $input, int $scale) {
    $result = TestingNumber::determineScale($input);

    expect($result)->toBe($scale);
})->with([
    ['1', 0],
    ['-1', 0],
    ['1.0', 1],
    ['-1.0', 1],
    ['1.00', 2],
    ['-1.00', 2],
    ['1.000', 3],
    ['-1.000', 3],
    ['1.0000', 4],
    ['-1.0000', 4],
    ['3453534.345346457465654353453459834759374', 33],
    ['3453534', 0],
]);
