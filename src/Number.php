<?php

namespace BradieTilley\BCMath;

use Stringable;

final class Number implements Stringable
{
    private const TEMPORARY_SCALE = 20;
    private const MAX_EXPANSION_SCALE = 10;

    public string $value;

    public int $scale;

    public function __construct(string|int $num)
    {
        $this->setValue($num);
    }

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

    private static function parseScale(string $value): int
    {
        return strlen(substr(strrchr($value, '.'), 1));
    }

    private function getScale(string $num, ?int $scale): int
    {
        return $scale ??= max($this->scale, self::parseScale($num));
    }

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

    public function add(Number|string|int $num, ?int $scale = null, int $roundingMode = PHP_ROUND_HALF_UP): Number
    {
        $scale = $this->getScale($num, $scale);
        $num = bcadd($this->value, (string) $num, self::TEMPORARY_SCALE);

        return new self(
            $this->roundTo($num, $scale, $roundingMode),
        );
    }

    public function sub(Number|string|int $num, ?int $scale = null, int $roundingMode = PHP_ROUND_HALF_UP): Number
    {
        $scale = $this->getScale($num, $scale);
        $num = bcsub($this->value, (string) $num, self::TEMPORARY_SCALE);

        return new self(
            $this->roundTo($num, $scale, $roundingMode),
        );
    }

    public function mul(Number|string|int $num, ?int $scale = null, int $roundingMode = PHP_ROUND_HALF_UP): Number
    {
        $scale = $this->getScale($num, $scale);
        $num = bcmul($this->value, (string) $num, self::TEMPORARY_SCALE);

        return new self(
            $this->roundTo($num, $this->scale + $scale, $roundingMode),
        );
    }

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

    public function mod(Number|string|int $num, ?int $scale = null, int $roundingMode = PHP_ROUND_HALF_UP): Number
    {
        $scale = $this->getScale($num, $scale);
        $num = bcmod($this->value, (string) $num, self::TEMPORARY_SCALE);

        return new self(
            $this->roundTo($num, $scale, $roundingMode),
        );
    }

    public function powmod(Number|string|int $exponent, Number|string|int $modulus): Number
    {
        $exponent = (string) $exponent;
        $modulus = (string) $modulus;
        $scale = $this->getScale($exponent, null);

        $result = bcpowmod($this->value, $exponent, $modulus, $scale);

        return new self($result);
    }

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

    public function floor(): Number
    {
        if (($pos = strpos($this->value, '.')) === false) {
            return new self($this->value);
        }

        return new self(
            substr($this->value, 0, $pos),
        );
    }

    public function ceil(): Number
    {
        if (($pos = strpos($this->value, '.')) === false) {
            return new self($this->value);
        }

        return (new self(
            substr($this->value, 0, $pos),
        ))->add(1);
    }

    public function round(int $precision = 0, int $mode = PHP_ROUND_HALF_UP): Number
    {
        return new self(
            $this->roundTo($this->value, $precision, $mode),
        );
    }

    public function comp(Number|string|int $num, ?int $scale = null): int
    {
        $num = (string) $num;
        $scale = $this->getScale($num, $scale);

        return bccomp($this->value, $num, $scale);
    }

    public function eq(Number|string|int $num, ?int $scale = null): bool
    {
        $comp = $this->comp($num, $scale);

        return $comp === 0;
    }

    public function gt(Number|string|int $num, ?int $scale = null): bool
    {
        $comp = $this->comp($num, $scale);

        return $comp > 0;
    }

    public function gte(Number|string|int $num, ?int $scale = null): bool
    {
        $comp = $this->comp($num, $scale);

        return $comp >= 0;
    }

    public function lt(Number|string|int $num, ?int $scale = null): bool
    {
        $comp = $this->comp($num, $scale);

        return $comp < 0;
    }

    public function lte(Number|string|int $num, ?int $scale = null): bool
    {
        $comp = $this->comp($num, $scale);

        return $comp <= 0;
    }

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

    public function __toString(): string
    {
        return $this->value;
    }
}
