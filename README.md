# Integration with WordPress plugin "Block Metadata" 

[![Build Status][ico-travis]][link-travis]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Software License][ico-license]](LICENSE.md)

<!--
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Total Downloads][ico-downloads]][link-downloads]
-->

Make available the data exporting features from WordPress plugin "Block Metadata" through the PoP API

## Install

Via Composer

``` bash
composer require leoloso/examples-for-pop
```

<!--
## Usage

``` php
```
-->

## Standards

[PSR-1](https://www.php-fig.org/psr/psr-1), [PSR-4](https://www.php-fig.org/psr/psr-4) and [PSR-12](https://www.php-fig.org/psr/psr-12).

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
composer test
```

## Static Analysis

Execute [phpstan](https://github.com/phpstan/phpstan) with level 5:

``` bash
composer analyse
```

To run checks for level 0 (or any level from 0 to 8):

``` bash
./vendor/bin/phpstan analyse -l 0 src tests
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email leo@getpop.org instead of using the issue tracker.

## Credits

- [Leonardo Losoviz][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/leoloso/examples-for-pop.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/leoloso/examples-for-pop/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/leoloso/examples-for-pop.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/leoloso/examples-for-pop.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/leoloso/examples-for-pop.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/leoloso/examples-for-pop
[link-travis]: https://travis-ci.org/leoloso/examples-for-pop
[link-scrutinizer]: https://scrutinizer-ci.com/g/leoloso/examples-for-pop/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/leoloso/examples-for-pop
[link-downloads]: https://packagist.org/packages/leoloso/examples-for-pop
[link-author]: https://github.com/leoloso
[link-contributors]: ../../contributors
