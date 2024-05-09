<?php

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
final class Number implements Stringable
{
    /**
     * A temporary maximum scale to perform misc operations with
     */
    private const TEMPORARY_SCALE = 20;

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
    private const MAX_EXPANSION_SCALE = 10;

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
        $this->setValue($num);
    }

    /**
     * Increase this number by the given number
     */
    public function add(Number|string|int $num, ?int $scale = null, int $roundingMode = PHP_ROUND_HALF_UP): Number
    {
        $scale = $this->getScale($num, $scale);
        $num = bcadd($this->value, (string) $num, self::TEMPORARY_SCALE);

        return new self(
            $this->roundTo($num, $scale, $roundingMode),
        );
    }

    /**
     * Subtract this number by the given number
     */
    public function sub(Number|string|int $num, ?int $scale = null, int $roundingMode = PHP_ROUND_HALF_UP): Number
    {
        $scale = $this->getScale($num, $scale);
        $num = bcsub($this->value, (string) $num, self::TEMPORARY_SCALE);

        return new self(
            $this->roundTo($num, $scale, $roundingMode),
        );
    }

    /**
     * Multiply this number by the given number
     */
    public function mul(Number|string|int $num, ?int $scale = null, int $roundingMode = PHP_ROUND_HALF_UP): Number
    {
        $scale = $this->getScale($num, $scale);
        $num = bcmul($this->value, (string) $num, self::TEMPORARY_SCALE);

        return new self(
            $this->roundTo($num, $this->scale + $scale, $roundingMode),
        );
    }

    /**
     * Divide this number by the given number
     */
    public function div(Number|string|int $num, ?int $scale = null, int $roundingMode = PHP_ROUND_HALF_UP): Number
    {
        $dividendScale = $this->scale;

        $num = bcdiv($this->value, (string) $num, self::TEMPORARY_SCALE);
        $num = substr($num, -10) === '0000000000' ? rtrim($num, '0') : $num;
        $resultScale = self::parseScale($num);

        $scale = $resultScale > self::MAX_EXPANSION_SCALE
            ? ($dividendScale + self::MAX_EXPANSION_SCALE)
            : $this->getScale($num, $scale);

        return new self(
            $this->roundTo($num, $scale, $roundingMode),
        );
    }

    /**
     * Reduce this number by the given modulus
     */
    public function mod(Number|string|int $num, ?int $scale = null, int $roundingMode = PHP_ROUND_HALF_UP): Number
    {
        $scale = $this->getScale($num, $scale);
        $num = bcmod($this->value, (string) $num, self::TEMPORARY_SCALE);

        return new self(
            $this->roundTo($num, $scale, $roundingMode),
        );
    }

    /**
     * Get the power of this number and given exponent and reduce by the
     * given modulus
     */
    public function powmod(Number|string|int $exponent, Number|string|int $modulus): Number
    {
        $exponent = (string) $exponent;
        $modulus = (string) $modulus;
        $scale = $this->getScale($exponent, null);

        $result = bcpowmod($this->value, $exponent, $modulus, $scale);

        return new self($result);
    }

    /**
     * Get the power of this number and given exponent
     */
    public function pow(Number|string|int $exponent, int $minScale, ?int $scale = null, int $roundingMode = PHP_ROUND_HALF_UP): Number
    {
        if ($exponent->eq(0)) {
            return new self(0);
        }

        $exponent = (string) $exponent;
        $scale = max($minScale, $this->getScale($this->value, null) * abs((int) $exponent));

        $baseScale = $this->scale;

        $num = bcpow($this->value, $exponent, self::TEMPORARY_SCALE);
        $num = substr($num, -10) === '0000000000' ? rtrim($num, '0') : $num;
        $resultScale = self::parseScale($num);

        $scale = $resultScale > self::MAX_EXPANSION_SCALE
            ? ($baseScale + self::MAX_EXPANSION_SCALE)
            : $this->getScale($num, $scale);

        return new self(
            $this->roundTo($num, $scale, $roundingMode),
        );
    }

    /**
     * Get the square root of this number.
     */
    public function sqrt(?int $scale = null, int $roundingMode = PHP_ROUND_HALF_UP): Number
    {
        $baseScale = $this->scale;
        $scale = $this->getScale($this->value, $scale);

        $num = bcsqrt($this->value, self::TEMPORARY_SCALE);
        $num = substr($num, -10) === '0000000000' ? rtrim($num, '0') : $num;
        $resultScale = self::parseScale($num);

        $scale = $resultScale > self::MAX_EXPANSION_SCALE
            ? ($baseScale + self::MAX_EXPANSION_SCALE)
            : $this->getScale($num, $scale ?? $baseScale);

        return new self(
            $this->roundTo($num, $scale, $roundingMode),
        );
    }

    /**
     * Round this number down to the nearest whole integer
     */
    public function floor(): Number
    {
        if (($pos = strpos($this->value, '.')) === false) {
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
        if (($pos = strpos($this->value, '.')) === false) {
            return new self($this->value);
        }

        return (new self(
            substr($this->value, 0, $pos),
        ))->add(1);
    }

    /**
     * Round this number to the given precision
     */
    public function round(int $precision = 0, int $mode = PHP_ROUND_HALF_UP): Number
    {
        return new self(
            $this->roundTo($this->value, $precision, $mode),
        );
    }

    /**
     * Compare this number with the given number.
     */
    public function comp(Number|string|int $num, ?int $scale = null): int
    {
        $num = (string) $num;
        $scale = $this->getScale($num, $scale);

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
     */
    public function format(?int $scale = null, int $roundingMode = PHP_ROUND_HALF_UP, string $decimalSeparator = '.', string $thousandsSeparator = ''): string
    {
        $scale = $this->getScale($this->value, $scale);

        $rounded = $this->add(0, $scale, $roundingMode);

        return number_format(
            $rounded->value,
            $scale,
            $decimalSeparator,
            $thousandsSeparator,
        );
    }

    /**
     * Get the string representation of this number
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Internal function to set the value.
     *
     * This does not exist in the RFC and is therefore private.
     */
    private function setValue(string|int $value): void
    {
        $scale = 0;

        if (is_int($value)) {
            $value = (string) $value;
        } else {
            $scale = self::parseScale($value);
        }

        $this->value = $value;
        $this->scale = $scale;
    }

    /**
     * Determine the scale of a number string by counting the number
     * of digits to the right of the period.
     *
     * This does not exist in the RFC and is therefore private.
     */
    private static function parseScale(string $value): int
    {
        return strlen(substr(strrchr($value, '.'), 1));
    }

    /**
     * Determine what scale to use:
     *
     * User-provided Scale ($scale), or
     * Greater of:
     *    the current scale ($this->scale), or
     *    the inbound number's scale (from $num)
     *
     * This does not exist in the RFC and is therefore private.
     */
    private function getScale(string $num, ?int $scale): int
    {
        return $scale ??= max($this->scale, self::parseScale($num));
    }

    /**
     * Round off the given number ($num) to the given $scale (or computed
     * scale) using the given rounding mode.
     *
     * The number is rounded off to the temporary scale (20) and is then
     * rounded off to the appropriate scale and returned in string form.
     *
     * This does not exist in the RFC and is therefore private.
     */
    private function roundTo(Number|string|int $num, ?int $scale, int $roundingMode): string
    {
        $num = (string) $num;
        $scale = $this->getScale($num, $scale);
        $num = bcadd($num, 0, self::TEMPORARY_SCALE);

        /** Temporary float solution */
        $num = (float) $num;
        $num = round($num, $scale, $roundingMode);
        $num = bcadd($num, 0, $scale);

        return $num;
    }
}
