# Doctrine CRUD

[![Build Status](https://img.shields.io/travis/iPublikuj/doctrine-crud.svg?style=flat-square)](https://travis-ci.org/iPublikuj/doctrine-crud)
[![Scrutinizer Code Coverage](https://img.shields.io/scrutinizer/coverage/g/iPublikuj/doctrine-crud.svg?style=flat-square)](https://scrutinizer-ci.com/g/iPublikuj/doctrine-crud/?branch=master)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/iPublikuj/doctrine-crud.svg?style=flat-square)](https://scrutinizer-ci.com/g/iPublikuj/doctrine-crud/?branch=master)
[![Latest Stable Version](https://img.shields.io/packagist/v/ipub/doctrine-crud.svg?style=flat-square)](https://packagist.org/packages/ipub/doctrine-crud)
[![Composer Downloads](https://img.shields.io/packagist/dt/ipub/doctrine-crud.svg?style=flat-square)](https://packagist.org/packages/ipub/doctrine-crud)
[![License](https://img.shields.io/packagist/l/ipub/doctrine-crud.svg?style=flat-square)](https://packagist.org/packages/ipub/doctrine-crud)

Implementation of CRUD system into [Doctrine3](https://github.com/doctrine/orm) with CRUD system.

## Installation

The best way to install ipub/doctrine-crud is using [Composer](http://getcomposer.org/):

```sh
$ composer require ipub/doctrine-crud
```

After that you have to register extension in config.neon.

```neon
extensions:
	doctrineCrud: IPub\DoctrineCrud\DI\DoctrineCrudExtension
```

## Documentation

Learn how to register and work with blameable behavior in [documentation](https://github.com/iPublikuj/doctrine-crud/blob/master/docs/en/index.md).

***
Homepage [https://www.ipublikuj.eu](https://www.ipublikuj.eu) and repository [http://github.com/iPublikuj/doctrine-crud](http://github.com/iPublikuj/doctrine-crud).
