# Incept Framework

[![Travis CI](https://travis-ci.com/inceptphp/framework.svg?branch=main)](https://travis-ci.com/inceptphp/framework)
[![Coverage Status](https://coveralls.io/repos/github/inceptphp/framework/badge.svg?branch=main)](https://coveralls.io/github/inceptphp/framework?branch=main)
[![Latest Stable Version](https://poser.pugx.org/inceptphp/framework/v/stable)](https://packagist.org/packages/inceptphp/framework)
[![Total Downloads](https://poser.pugx.org/inceptphp/framework/downloads)](https://packagist.org/packages/inceptphp/framework)
[![Latest Unstable Version](https://poser.pugx.org/inceptphp/framework/v/unstable)](https://packagist.org/packages/inceptphp/framework)
[![License](https://poser.pugx.org/inceptphp/framework/license)](https://packagist.org/packages/inceptphp/framework)

----

<a name="contributing"></a>
# Contributing

Bug fixes will be reviewed as soon as possible. Minor features will also be considered, but give me time to review it and get back to you. Major features will **only** be considered on the `master` branch.

1. Fork the Repository.
2. Fire up your local terminal and switch to the version you would like to
contribute to.
3. Make your changes.
4. Always make sure to sign-off (-s) on all commits made (git commit -s -m "Commit message")

## Making pull requests

1. Please ensure to run [phpunit](https://phpunit.de/) and
[phpcs](https://github.com/squizlabs/PHP_CodeSniffer) before making a pull request.
2. Push your code to your remote forked version.
3. Go back to your forked version on GitHub and submit a pull request.
4. All pull requests will be passed to [Travis CI](https://travis-ci.com/github/inceptphp/framework) to be tested. Also note that [Coveralls](https://coveralls.io/github/inceptphp/framework) is also used to analyze the coverage of your contribution.

## Setting Up PHPUnit

```bash
$ composer global require phpunit/phpunit
$ export PATH=~/.composer/vendor/bin:$PATH
$ XDEBUG_MODE=coverage phpunit -d memory_limit=512M --coverage-html ../../../public/test
```
