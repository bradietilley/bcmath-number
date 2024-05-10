<?php

use BradieTilley\BCMath\Number;

test('Number can format', function (string|int $input, int $scale, int $roundingMode, ?string $decimal, ?string $thousands, string $expect) {
    $num = new Number($input);
    $result = $num->format($scale, $roundingMode, $decimal ?? '.', $thousands ?? '');

    expect($result)->toBe($expect);
})->with([
    /**
     * Basic formatting
     */
    ['12.34', 2, PHP_ROUND_HALF_UP, null, null, '12.34' ],
    ['-12.34', 2, PHP_ROUND_HALF_UP, null, null, '-12.34' ],

    /**
     * Varying rounding modes
     */
    ['-12.349475', 5, PHP_ROUND_HALF_UP, null, null, '-12.34948' ],
    ['-12.349475', 5, PHP_ROUND_HALF_DOWN, null, null, '-12.34947' ],
    ['-12.349475', 5, PHP_ROUND_HALF_ODD, null, null, '-12.34947' ],
    ['-12.349475', 5, PHP_ROUND_HALF_EVEN, null, null, '-12.34948' ],

    /**
     * Varying formatting arguments
     */
    ['328497329743.123456789012345678', 5, PHP_ROUND_HALF_UP, null, null, '328497329743.12346'],
    ['328497329743.123456789012345678', 5, PHP_ROUND_HALF_UP, '-', null, '328497329743-12346'],
    ['328497329743.123456789012345678', 5, PHP_ROUND_HALF_UP, '.', ',', '328,497,329,743.12346'],
    ['1328497329743.123456789012345678', 5, PHP_ROUND_HALF_UP, '.', ',', '1,328,497,329,743.12346'],
    ['-328497329743.123456789012345678', 5, PHP_ROUND_HALF_UP, '.', ',', '-328,497,329,743.12346'],
]);
