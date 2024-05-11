<?php

use Tests\Fixtures\TestingNumber;

test('Number can remove superfluous precision', function (string $input, int $minScale, string $expect) {
    $result = TestingNumber::removeSuperfluousPrecision($input, $minScale);

    expect($result)->toBe($expect);
})->with([
    [ '3.45', 2, '3.45' ],
    [ '3.4500000000000', 2, '3.45' ],
    [ '3.4500000000001', 2, '3.4500000000001' ],
    [ '3.4500000000000', 4, '3.4500' ],
    [ '3.4543789543987', 4, '3.4543789543987' ],
    [ '-6.29', 2, '-6.29' ],
    [ '-6.2900000000000', 2, '-6.29' ],
    [ '-6.2900000000001', 2, '-6.2900000000001' ],
    [ '-6.2900000000000', 4, '-6.2900' ],
    [ '-6.2943789543987', 4, '-6.2943789543987' ],
    [ '1.23', 0, '1.23'],
    [ '1.00', 0, '1'],
    [ '1', 0, '1'],
]);
