<?php

declare(strict_types=1);

use BradieTilley\BCMath\Number;

if (\PHP_VERSION_ID >= 80400) {
    return;
}

if (! class_exists(BCMath\Number::class)) {
    class_alias(Number::class, BCMath\Number::class);
}
