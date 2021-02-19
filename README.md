
# PHPTAL - Template Attribute Language for PHP

[![Monthly Downloads](https://poser.pugx.org/scn/phptal/d/monthly)](https://packagist.org/packages/scn/phptal)
[![License](https://poser.pugx.org/scn/phptal/license)](LICENSE)
[![unittest](https://github.com/SC-Networks/PHPTAL/actions/workflows/unittests.yml/badge.svg)](https://github.com/SC-Networks/PHPTAL/actions/workflows/unittests.yml)

Requirements
============

To use PHPTAL in your projects, you will require PHP 7.3 or later.

If you want to use the builtin internationalisation system (I18N), the php-gettext extension must be installed or compiled into PHP (`--with-gettext`).

Composer install (recommended)
==============================

You can install this package by using [Composer](http://getcomposer.org).
Link to Packagist: https://packagist.org/packages/scn/phptal

```sh
composer require scn/phptal
```

Getting the latest development version
======================================

You can find the latest development version on github:

	https://github.com/SC-Networks/PHPTAL

Addition development requirements (optional)
============================================

If you would like to generate the offical html/text handbook by calling
`make doc`, you will need to install the `xmlto` package. Please use
your operating systems package manager to install it.

If you'd like to create the sourcecode documentation, you need the `phpDocumentor.phar` executable
in your `$PATH`.