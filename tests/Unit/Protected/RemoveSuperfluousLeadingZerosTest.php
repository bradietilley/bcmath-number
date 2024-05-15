<?php

use Tests\Fixtures\TestingNumber;

test('Number can remove superfluous leading zeros', function (string|int $input, string $expect) {
    $result = TestingNumber::removeSuperfluousLeadingZeros($input);

    expect($result)->toBe($expect);
})->with([
    /** Integers */
    [ -3000, '-3000' ],
    [ -1, '-1' ],
    [ 0, '0' ],
    [ 1, '1' ],
    [ 3000, '3000' ],

    /** String Integers */
    [ '-3000', '-3000' ],
    [ '-1', '-1' ],
    [ '0', '0' ],
    [ '1', '1' ],
    [ '3000', '3000' ],

    /** String Floats */
    [ '-3000.00', '-3000.00' ],
    [ '-1.0', '-1.0' ],
    [ '0.0', '0.0' ],
    [ '1.0', '1.0' ],
    [ '3000.000', '3000.000' ],

    /** Leading zeros */
    [ '-0000001.0', '-1.0' ],
    [ '0000000.0', '0.0' ],
    [ '0000001.0', '1.0' ],
    [ '-0000001', '-1' ],
    [ '0000000', '0' ],
    [ '0000001', '1' ],
]);
