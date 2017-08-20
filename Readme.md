# Convert your translation files

[![Latest Version](https://img.shields.io/github/release/php-translation/converter.svg?style=flat-square)](https://github.com/php-translation/converter/releases)
[![Build Status](https://img.shields.io/travis/php-translation/converter.svg?style=flat-square)](https://travis-ci.org/php-translation/converter)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/php-translation/converter.svg?style=flat-square)](https://scrutinizer-ci.com/g/php-translation/converter)
[![Quality Score](https://img.shields.io/scrutinizer/g/php-translation/converter.svg?style=flat-square)](https://scrutinizer-ci.com/g/php-translation/converter)
[![Total Downloads](https://img.shields.io/packagist/dt/php-translation/converter.svg?style=flat-square)](https://packagist.org/packages/php-translation/converter)

**Don't you hate all the different translations formats? Are you stuck with JMSTranslatorBundle? If so, this is the 
tool for you!**

This little tool can convert your translation files from one format to the excellent XLIFF 2.0. A perfect use case is 
when you migrating from JMSTranslatorBundle to [PHP-translation](http://php-translation.readthedocs.io/).

### Install

```bash
composer require php-translation/converter
```

### Use

Just run the command like below:

```bash
# Example
./vendor/bin/translation-converter [input_dir] [output_dir] [format]

# Convert from JMSTranslationBundle
./vendor/bin/translation-converter app/Resources/translations app/Resources/translations-new jms

# Convert from Yaml
./vendor/bin/translation-converter app/Resources/translations app/Resources/translations-new yml
```

### Documentation

Read our documentation at [http://php-translation.readthedocs.io](http://php-translation.readthedocs.io/en/latest/).

### Contribute

Do you want to make a change? This repository is READ ONLY. Submit your 
pull request to [php-translation/platform-adapter](https://github.com/php-translation/platform-adapter).
