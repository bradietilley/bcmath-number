<?php

namespace Tests\Fixtures;

use BradieTilley\BCMath\Number;
use ReflectionMethod;

/**
 * @method static int determineScale(Number|string|int $value)
 * @method static string roundTo(Number|string|int $num, ?int $scale, int $roundingMode)
 * @method static Number rounded(Number|string|int $num, ?int $scale, int $roundingMode)
 * @method static string formatTo(Number|string|int $num, string $decimalSeparator = self::DECIMAL_SEPARATOR, string $thousandsSeparator = self::THOUSANDS_SEPARATOR)
 * @method static array parseFragments(Number|string|int $num)
 *
 * @mixin Number
 */
class TestingNumber
{
    public function __construct(public readonly Number $value)
    {
    }

    public static function __callStatic($name, $arguments)
    {
        return (new ReflectionMethod(Number::class, $name))->invoke(null, ...$arguments);
    }

    public function __call($name, $arguments)
    {
        return (new ReflectionMethod($this->value, $name))->invoke($this->value, ...$arguments);
    }
}
