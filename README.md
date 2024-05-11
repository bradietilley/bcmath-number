# BCMath Number

A drop-in replacement for the upcoming `BCMath\Number` object type in PHP 8.4

![Static Analysis](https://github.com/bradietilley/bcmath-number/actions/workflows/static.yml/badge.svg)
![Tests](https://github.com/bradietilley/bcmath-number/actions/workflows/tests.yml/badge.svg)


## Introduction

The `BCMath\Number` object type is not yet confirmed ([RFC](https://php.watch/rfcs/support_object_type_in_bcmath)). It is also unclear about the exact implementation deatils so this package is not a perfect replica however I've aimed to achieve an exact alternative to `BCMath\Number` not necessarily as a backwards-compatible polyfill or shim but to be used as dependency before `BCMath\Number` is available.

This package is available as `BradieTilley\BCMath\Number` and, until PHP 8.4, `BCMath\Number`.


## Installation

```
composer require bradietilley/bcmath-number
```


## Documentation

Refer to the [RFC](https://php.watch/rfcs/support_object_type_in_bcmath) for general usage examples. The gist of it is:

```php
use BCMath\Number; // or use BradieTilley\BCMath\Number;

$number = new Number('34.465');
$result = $number->add('76.2');

echo (string) $result; // 110.665

// Number class is immutable so the original $number value remains. Resulting value are returned in a new object.
echo (string) $number; // 34.465
```

## Author

- [Bradie Tilley](https://github.com/bradietilley)
- [Owen Voke](https://github.com/owenvoke)
