[![PHP version](https://img.shields.io/badge/PHP-%3E%3D7-8892BF.svg?style=flat-square)](http://php.net)
[![Latest Version](https://img.shields.io/packagist/v/juliangut/slim-exception.svg?style=flat-square)](https://packagist.org/packages/juliangut/slim-exception)
[![License](https://img.shields.io/github/license/juliangut/slim-exception.svg?style=flat-square)](https://github.com/juliangut/slim-exception/blob/master/LICENSE)

[![Build Status](https://img.shields.io/travis/juliangut/slim-exception.svg?style=flat-square)](https://travis-ci.org/juliangut/slim-exception)
[![Style Check](https://styleci.io/repos/XXXXXXXX/shield)](https://styleci.io/repos/XXXXXXXX)
[![Code Quality](https://img.shields.io/scrutinizer/g/juliangut/slim-exception.svg?style=flat-square)](https://scrutinizer-ci.com/g/juliangut/slim-exception)
[![Code Coverage](https://img.shields.io/coveralls/juliangut/slim-exception.svg?style=flat-square)](https://coveralls.io/github/juliangut/slim-exception)

[![Total Downloads](https://img.shields.io/packagist/dt/juliangut/slim-exception.svg?style=flat-square)](https://packagist.org/packages/juliangut/slim-exception/stats)
[![Monthly Downloads](https://img.shields.io/packagist/dm/juliangut/slim-exception.svg?style=flat-square)](https://packagist.org/packages/juliangut/slim-exception/stats)

# slim-exception

HTTP aware exceptions and whoops based exception handling for Slim Framework

## Installation

### Composer

```
composer require juliangut/slim-exception
```

If you want extra information on errors output you need [whoops](https://github.com/filp/whoops) for the new error handlers 

```
composer require filp/whoops
```

## Usage

Require composer autoload file

```php
require './vendor/autoload.php';

use Jgut\Slim\Exception\Handler\ErrorHandler;
use Jgut\Slim\Exception\Handler\NotAllowedHandler;
use Jgut\Slim\Exception\Handler\NotFoundHandler;

// Create Slim App

$container = $app->getContainer();

$container['errorHandler'] = $container['phpErrorHandler'] = new ErrorHandler();
$container['notAllowedHandler'] = new NotAllowedHandler();
$container['notFoundHandler'] = new NotFoundHandler();

// ...

$app->run();
```

## HTTP exceptions

### Identifier

Exceptions have a unique identifier

```php
$exception->getIdentifier();
```

By displaying this identifier and at the same time logging the exceptions allows to have more information over the erroneous situation

### HTTP status

Exceptions carry a HTTP status code which can be directly used in Response objects

```php
$exception->getHttpStatusCode();
```

### Factory

```php
use Jgut\Slim\Exception\HttpExceptionFactory;

$exceptionCode = 10; // Internal code
$httpStatusCode = 401; // Unauthorized
throw HttpExceptionFactory::create('You shall not pass!', $exceptionCode, $httpStatusCode);
```

In order to simplify HTTP exception creation and assure correct HTTP status code selection there are several shortcut creation methods

```php
throw HttpExceptionFactory::unauthorized('You shall not pass!');
throw HttpExceptionFactory::notAcceptable('Throughput reached');
throw HttpExceptionFactory::unprocessableEntity('Already exists');
``` 

## Handlers

Default Slim error handlers are quite simple, doesn't really provide any useful information during development and at the same time are ugly when on production.

The three new handlers behaves identical to the ones officially shipped with Slim, the too allow you to customize response error content but its true power comes when bundled with a dumper. The selection of return content type is automatically done based on request "Accept" header

### Dumpers

In order to display errors with as much useful information as possible you can set an exception dumper on handlers. This dumper will set error information on response content based on request "Accept" header and gracefully fallback if don't know how to format output.

Currently the dumper is based on whoops but you can create your own by implementing `Jgut\Slim\Exception\Dumper\Dumper` interface

```php
require './vendor/autoload.php';

use Jgut\Slim\Exception\Handler\ErrorHandler;
use Jgut\Slim\Exception\Handler\NotAllowedHandler;
use Jgut\Slim\Exception\Handler\NotFoundHandler;
use Jgut\Slim\Exception\Dumper\Whoops\ExceptionDumper;
use Jgut\Slim\Exception\Dumper\Whoops\HtmlHandler;
use Jgut\Slim\Exception\Dumper\Whoops\JsonHandler;
use Jgut\Slim\Exception\Dumper\Whoops\TextHandler;
use Whoops\Run as Whoops;

$whoops = new Whoops();
$whoops->pushHandler(new TextHandler());
$whoops->pushHandler(new JsonHandler());
$whoops->pushHandler(new HtmlHandler());
$dumper = new ExceptionDumper($whoops);

$exceptionHandler = new ErrorHandler();
$exceptionHandler->setDumper($dumper);

$notAllowedHandler = new NotAllowedHandler();
$notAllowedHandler->setDumper($dumper);

$notFoundHandler = new NotFoundHandler();
$notFoundHandler->setDumper($dumper);
```

## Contributing

Found a bug or have a feature request? [Please open a new issue](https://github.com/juliangut/slim-exception/issues). Have a look at existing issues before.

See file [CONTRIBUTING.md](https://github.com/juliangut/slim-exception/blob/master/CONTRIBUTING.md)

## License

See file [LICENSE](https://github.com/juliangut/slim-exception/blob/master/LICENSE) included with the source code for a copy of the license terms.
