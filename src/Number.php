<?php

declare(strict_types=1);

namespace BradieTilley\BCMath;

use Stringable;

/**
 * A drop-in replacement for the upcoming BCMath\Number class.
 *
 * This mimicks the interface of upcoming Number class however
 * it may be lacking in accuracy of how the upcoming Number
 * class handles certain rounding and alike.
 *
 * @see \BCMath\Number (Coming in PHP 8.4)
 */
final readonly class Number implements Stringable
{
    /**
     * A temporary maximum scale to perform misc operations with.
     */
    protected const TEMPORARY_SCALE = 20;

    /**
     * From RFC:
     *
     * For div, pow, and sqrt, the scale of the calculation result
     * may be infinite. Therefore, these three calculations have the
     * concept of “maximum expansion scale” of the scale. This is
     * the number of digits to extend relative to the original scale
     * of the left operand. This is the value used only if no scale
     * is specified and cannot be changed from userland.
     */
    protected const MAX_EXPANSION_SCALE = 10;

    /**
     * Constant to represent one in string form
     */
    protected const ONE = '1';

    /**
     * Constant to represent zero in string form
     */
    protected const ZERO = '0';

    /**
     * The numerical prefix that shows that a number is negative
     */
    protected const SYMBOL_NEGATIVE = '-';

    protected const DECIMAL_SEPARATOR = '.';

    protected const THOUSANDS_SEPARATOR = '';

    /**
     * The number in string form e.g. "2.0001" or "45"
     */
    public string $value;

    /**
     * The precision of the number e.g. "9.5000" will have a scale
     * of 4 (i.e. the four digits in "5000").
     */
    public int $scale;

    public function __construct(string|int $num)
    {
        $this->scale = self::determineScale($num);
        $this->value = (string) $num;
    }

    /**
     * Increase this number by the given number
     *
     * @param int<1, 4> $roundingMode
     */
    public function add(Number|string|int $num, ?int $scale = null, int $roundingMode = PHP_ROUND_HALF_UP): Number
    {
        $scale ??= max($this->scale, self::determineScale($num));
        $num = bcadd($this->value, (string) $num, self::TEMPORARY_SCALE);

        return $this->rounded($num, $scale, $roundingMode);
    }

    /**
     * Subtract this number by the given number
     *
     * @param int<1, 4> $roundingMode
     */
    public function sub(Number|string|int $num, ?int $scale = null, int $roundingMode = PHP_ROUND_HALF_UP): Number
    {
        $scale = max($this->scale, $scale ??= self::determineScale($num));
        $num = bcsub($this->value, (string) $num, self::TEMPORARY_SCALE);

        return $this->rounded($num, $scale, $roundingMode);
    }

    /**
     * Multiply this number by the given number
     *
     * @param int<1, 4> $roundingMode
     */
    public function mul(Number|string|int $num, ?int $scale = null, int $roundingMode = PHP_ROUND_HALF_UP): Number
    {
        $scale = $this->scale + $scale ??= self::determineScale($num);
        $num = bcmul($this->value, (string) $num, self::TEMPORARY_SCALE);

        return $this->rounded($num, $scale, $roundingMode);
    }

    /**
     * Divide this number by the given number
     *
     * @param int<1, 4> $roundingMode
     */
    public function div(Number|string|int $num, ?int $scale = null, int $roundingMode = PHP_ROUND_HALF_UP): Number
    {
        /**
         * The dividend (this) scale is used when the scale of the division result
         * exceeds the MAX_EXPANSION_SCALE.
         */
        $dividendScale = $this->scale;

        /**
         * First try the division with a large scale
         */
        $num = bcdiv($this->value, (string) $num, self::TEMPORARY_SCALE);

        /**
         * If the result has a considerable amount of trailing zeros, consider it
         * a clean division and round off to the first trailing non-zero.
         *
         * E.g. 2482 / 680 = 3.65   using bcdiv (scale 20): 3.65000000000000000000
         * This value is a clean division and can be trimmed to 3.65
         *
         * E.g. 2482 / 681 = 3.6446402349...   using bcdiv (scale 20): 3.64464023494860499265
         * This value is not a clean division and cannot be trimmed beyond the temporary scale
         */
        $num = rtrim($num, self::ZERO);
        $num = $scale !== null ? str_pad($num, $scale, self::ZERO, STR_PAD_RIGHT) : $num;

        /**
         * With the (potentially) rounded off value we can now determine what scale we are
         * dealing with. Using the examples above:
         *
         * E.g. 3.65000000000000000000 -> 3.65 -> 2
         * E.g. 3.64464023494860499265 -> 3.64464023494860499265 -> 20 (TEMPORARY_SCALE)
         */
        $resultScale = self::determineScale($num);

        /**
         * Next we determine the final scale.
         *
         * If the user provides one, we'll use that scale. No if buts or maybes.
         *
         * If the user doesn't supply one, a suitable scale is derived:
         *
         *      If the division resulted in a clean division, such as one with a considerably
         *      low scale (i.e. the scale is < the MAX_EXPANSION_SCALE), we then use the
         *      dividend scale or the resulting scale, whichever is greater.
         *
         *      If the division resulted in a messy division, such as one with a considerably
         *      high scale (i.e. the scale is >= the MAX_EXPANSION_SCALE), we then use the
         *      sum of the dividend scale and the MAX_EXPANSION_SCALE.
         */
        if ($scale === null) {
            $scale = $resultScale >= self::MAX_EXPANSION_SCALE
                ? ($dividendScale + self::MAX_EXPANSION_SCALE)
                : max($dividendScale, $resultScale);
        }

        return $this->rounded($num, $scale, $roundingMode);
    }

    /**
     * Reduce this number by the given modulus
     *
     * @param int<1, 4> $roundingMode
     */
    public function mod(Number|string|int $num, ?int $scale = null, int $roundingMode = PHP_ROUND_HALF_UP): Number
    {
        $scale = max($this->scale, $scale ?? self::determineScale($num));
        $num = bcmod($this->value, (string) $num, self::TEMPORARY_SCALE);

        return $this->rounded($num, $scale, $roundingMode);
    }

    /**
     * Get the power of this number and given exponent and reduce by the
     * given modulus
     */
    public function powmod(Number|string|int $exponent, Number|string|int $modulus): Number
    {
        $exponent = (string) $exponent;
        $modulus = (string) $modulus;
        $scale = self::determineScale($exponent);

        $result = bcpowmod($this->value, $exponent, $modulus, $scale);

        return new self($result);
    }

    /**
     * Get the power of this number and given exponent
     *
     * @param int<1, 4> $roundingMode
     */
    public function pow(Number|string|int $exponent, int $minScale, ?int $scale = null, int $roundingMode = PHP_ROUND_HALF_UP): Number
    {
        $exponent = (string) $exponent;

        if ($exponent === self::ZERO) {
            return new self(0);
        }

        $exponent = (string) $exponent;
        $scale = max($minScale, self::determineScale($this->value) * abs((int) $exponent));

        $baseScale = $this->scale;

        $num = bcpow($this->value, $exponent, self::TEMPORARY_SCALE);
        $num = self::removeSuperfluousPrecision($num, $baseScale);
        $resultScale = self::determineScale($num);

        $scale = ($resultScale > self::MAX_EXPANSION_SCALE)
            ? ($baseScale + self::MAX_EXPANSION_SCALE)
            : $scale;

        return $this->rounded($num, $scale, $roundingMode);
    }

    /**
     * Get the square root of this number.
     *
     * @param int<1, 4> $roundingMode
     */
    public function sqrt(?int $scale = null, int $roundingMode = PHP_ROUND_HALF_UP): Number
    {
        $baseScale = $this->scale;

        $num = bcsqrt($this->value, self::TEMPORARY_SCALE);
        $num = self::removeSuperfluousPrecision($num, $baseScale);
        $resultScale = self::determineScale($num);

        $scale = $resultScale > self::MAX_EXPANSION_SCALE
            ? ($baseScale + self::MAX_EXPANSION_SCALE)
            : $scale ?? max($this->scale, self::determineScale($num));

        return $this->rounded($num, $scale, $roundingMode);
    }

    /**
     * Round this number down to the nearest whole integer
     */
    public function floor(): Number
    {
        if (($pos = strpos($this->value, self::DECIMAL_SEPARATOR)) === false) {
            return new self($this->value);
        }

        return new self(
            substr($this->value, 0, $pos),
        );
    }

    /**
     * Round this number up to the nearest whole integer
     */
    public function ceil(): Number
    {
        if (($pos = strpos($this->value, self::DECIMAL_SEPARATOR)) === false) {
            return new self($this->value);
        }

        return (new self(
            substr($this->value, 0, $pos),
        ))->add(1);
    }

    /**
     * Round this number to the given precision
     *
     * @param int<1, 4> $mode
     */
    public function round(int $precision = 0, int $mode = PHP_ROUND_HALF_UP): Number
    {
        return $this->rounded($this->value, $precision, $mode);
    }

    /**
     * Compare this number with the given number.
     */
    public function comp(Number|string|int $num, ?int $scale = null): int
    {
        $num = (string) $num;
        $scale = $scale ?? self::determineScale($num);

        return bccomp($this->value, $num, $scale);
    }

    /**
     * Check if this number is equal to the given number.
     */
    public function eq(Number|string|int $num, ?int $scale = null): bool
    {
        $comp = $this->comp($num, $scale);

        return $comp === 0;
    }

    /**
     * Check if this number is greater than the given number.
     */
    public function gt(Number|string|int $num, ?int $scale = null): bool
    {
        $comp = $this->comp($num, $scale);

        return $comp > 0;
    }

    /**
     * Check if this number is greater than or equal to the given number.
     */
    public function gte(Number|string|int $num, ?int $scale = null): bool
    {
        $comp = $this->comp($num, $scale);

        return $comp >= 0;
    }

    /**
     * Check if this number is less than the given number.
     */
    public function lt(Number|string|int $num, ?int $scale = null): bool
    {
        $comp = $this->comp($num, $scale);

        return $comp < 0;
    }

    /**
     * Check if this number is less than or equal to the given number.
     */
    public function lte(Number|string|int $num, ?int $scale = null): bool
    {
        $comp = $this->comp($num, $scale);

        return $comp <= 0;
    }

    /**
     * Perform a number format
     *
     * @see number_format()
     *
     * @param int<1, 4> $roundingMode
     */
    public function format(?int $scale = null, int $roundingMode = PHP_ROUND_HALF_UP, string $decimalSeparator = self::DECIMAL_SEPARATOR, string $thousandsSeparator = self::THOUSANDS_SEPARATOR): string
    {
        $scale ??= self::determineScale($this->value);
        $rounded = $this->add(0, $scale, $roundingMode);

        return self::formatTo($rounded, $decimalSeparator, $thousandsSeparator);
    }

    /**
     * Get the string representation of this number
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Determine the scale of a number string by counting the number
     * of digits to the right of the period.
     *
     * This does not exist in the RFC and is therefore protected.
     */
    protected static function determineScale(Number|string|int $value): int
    {
        $value = (string) $value;
        $pos = strrchr($value, self::DECIMAL_SEPARATOR);

        if ($pos === false) {
            return 0;
        }

        return strlen(substr($pos, 1));
    }

    /**
     * Round off the given number ($num) to the given $scale (or computed
     * scale) using the given rounding mode.
     *
     * The number is rounded off to the temporary scale (20) and is then
     * rounded off to the appropriate scale and returned in string form.
     *
     * This does not exist in the RFC and is therefore protected.
     *
     * @param int<1, 4> $roundingMode
     */
    protected static function roundTo(Number|string|int $num, ?int $scale, int $roundingMode): string
    {
        $num = (string) $num;
        $scale ??= self::determineScale($num);

        [$wholeNumber, $decimalNumber] = self::parseFragments($num);

        $decimalFixed = substr($decimalNumber, 0, $scale - 1);
        $decimalRounding = substr($decimalNumber, $scale - 1);

        if ($decimalRounding === '') {
            return bcdiv($num, self::ONE, $scale);
        }

        $negative = str_starts_with($wholeNumber, self::SYMBOL_NEGATIVE) ? self::SYMBOL_NEGATIVE : '';
        $decimalRounding = $negative.substr($decimalRounding, 0, 1).self::DECIMAL_SEPARATOR.substr($decimalRounding, 1);
        $decimalRounding = (float) $decimalRounding;

        $rounded = (string) round($decimalRounding, 0, $roundingMode);
        $rounded = ltrim($rounded, self::SYMBOL_NEGATIVE);

        $rounded = $wholeNumber.self::DECIMAL_SEPARATOR.$decimalFixed.$rounded;
        $rounded = bcdiv($rounded, self::ONE, $scale);

        return $rounded;
    }

    /**
     * Round the given value to the provide scale or calculated scale.
     *
     * @param int<1, 4> $roundingMode
     */
    protected static function rounded(Number|string|int $num, ?int $scale, int $roundingMode): Number
    {
        $rounded = self::roundTo($num, $scale, $roundingMode);

        return new self($rounded);
    }

    /**
     * Format the given number with the given configuration
     */
    protected static function formatTo(Number|string|int $num, string $decimalSeparator = self::DECIMAL_SEPARATOR, string $thousandsSeparator = self::THOUSANDS_SEPARATOR): string
    {
        $num = (string) $num;

        [$wholeNumber, $decimalNumber] = static::parseFragments($num);

        $wholeNumber = strrev($wholeNumber);
        $pending = '';

        for ($i = 0; $i < strlen($wholeNumber); $i++) {
            $char = $wholeNumber[$i];

            if ($char === self::SYMBOL_NEGATIVE) {
                $pending .= $char;

                break;
            }

            if ($i > 0 && $i % 3 === 0) {
                $pending .= $thousandsSeparator;
            }

            $pending .= $char;
        }

        $wholeNumber = strrev($pending);

        return $wholeNumber.$decimalSeparator.$decimalNumber;
    }

    /**
     * Parse the given number and extract the whole number and decimal number
     *
     * @return array<int, string>
     */
    protected static function parseFragments(Number|string|int $num): array
    {
        $num = (string) $num;
        $pos = strpos($num, self::DECIMAL_SEPARATOR);
        $wholeNumber = $num;
        $decimalNumber = '';

        if ($pos !== false) {
            $wholeNumber = substr($num, 0, $pos);
            $decimalNumber = substr($num, $pos + 1);
        }

        return [
            $wholeNumber,
            $decimalNumber,
        ];
    }

    /**
     * Certain mathematical equations are performed with a temp scale (20) to ensure no
     * decimal points are lost. However this adds a huge trailing decimal string of many
     * zeros. Because the precision of the equation's final output may be influenced by
     * whether or not the computed number needs a high precision, we will remove any of
     * the trailing decimal zeros down, to the minimum scale provided, to allow us to
     * later determine the true precision/scale of the number.
     *
     * E.g.
     *      "3.00"                  with scale 0             -> "3"
     *      "3.00"                  with scale 2             -> "3.00"
     *      "3.45"                  with scale 2             -> "3.45"
     *      "3.4500000000000"       with scale 2             -> "3.45"
     *      "3.4500000000000"       with scale 4             -> "3.4500"
     *      "3.4543789543987"       with scale 4             -> "3.454378954398753453"
     */
    protected static function removeSuperfluousPrecision(string $num, int $minScale): string
    {
        [$wholeNumber, $decimalNumber] = self::parseFragments($num);

        if ($decimalNumber === '') {
            return $wholeNumber;
        }

        $decimalKeep = substr($decimalNumber, 0, $minScale);

        $decimalTail = substr($decimalNumber, $minScale);
        $decimalTail = rtrim($decimalTail, self::ZERO);

        if ($minScale === 0 && $decimalTail === '') {
            return $wholeNumber;
        }

        return $wholeNumber.self::DECIMAL_SEPARATOR.$decimalKeep.$decimalTail;
    }
}
