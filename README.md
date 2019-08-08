[![PHP version](https://img.shields.io/badge/PHP-%3E%3D7.1-8892BF.svg?style=flat-square)](http://php.net)
[![Latest Version](https://img.shields.io/packagist/v/juliangut/slim-exception.svg?style=flat-square)](https://packagist.org/packages/juliangut/slim-exception)
[![License](https://img.shields.io/github/license/juliangut/slim-exception.svg?style=flat-square)](https://github.com/juliangut/slim-exception/blob/master/LICENSE)

[![Build Status](https://img.shields.io/travis/juliangut/slim-exception.svg?style=flat-square)](https://travis-ci.org/juliangut/slim-exception)
[![Style Check](https://styleci.io/repos/98827578/shield)](https://styleci.io/repos/98827578)
[![Code Quality](https://img.shields.io/scrutinizer/g/juliangut/slim-exception.svg?style=flat-square)](https://scrutinizer-ci.com/g/juliangut/slim-exception)
[![Code Coverage](https://img.shields.io/coveralls/juliangut/slim-exception.svg?style=flat-square)](https://coveralls.io/github/juliangut/slim-exception)

[![Total Downloads](https://img.shields.io/packagist/dt/juliangut/slim-exception.svg?style=flat-square)](https://packagist.org/packages/juliangut/slim-exception/stats)
[![Monthly Downloads](https://img.shields.io/packagist/dm/juliangut/slim-exception.svg?style=flat-square)](https://packagist.org/packages/juliangut/slim-exception/stats)

# slim-exception

Alternative Slim error handling with better response format negotiation and development support

## Installation

### Composer

```
composer require juliangut/slim-exception
```

## Usage

Require composer autoload file

```php
require './vendor/autoload.php';

use Jgut\Slim\Exception\Handler\ErrorHandler;
use Jgut\Slim\Exception\Whoops\Handler\ErrorHandler as WhoopsErrorHandler;
use Negotiation\Negotiator;
use Slim\Factory\AppFactory;
use Whoops\Run as Whoops;

// Instantiate the app
$app = AppFactory::create();

// ...

$callableResolver = $app->getCallableResolver();
$responseFactory = $app->getResponseFactory();

$errorHandler = $inDevelopment
    ? new WhoopsErrorHandler($callableResolver, $responseFactory, new Negotiator(), new Whoops())
    : new ErrorHandler($callableResolver, $responseFactory, new Negotiator());

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, false, false);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

// ...

$app->run();
```

### Renderers

* `Html` responds to text/html and application/xhtml+xml content types
* `Json` responds to application/json, text/json and application/x-json content types
* `Text` responds to text/plain content type
* `Xml` responds to application/xml, text/xml and application/x-xml content types

### Whoops

Development environment deserves a better, more informative error handling.

[Whoops](https://github.com/filp/whoops) is a great tool for this purpose and its usage is integrated in this package. There is an special Whoops error handler which can be used as default exception handler for development

The example of how to include Whoops error handler is in the code above

**_Consider requiring Whoops and var-dumper in production as well to benefit from better and richer stack traces in logs_**

For you to use this handler you'll need to require whoops first. Additionally symfony's var-dumper plays nice with whoops so require it too

```
composer require filp/whoops
composer require symfony/var-dumper
```

## Handle all errors/exceptions

In order to fully integrate error handling with the environment you can register ExceptionHandler globally. In this way any triggered and unhandled error will be captured and treated by the error handler

```php
use Jgut\Slim\Exception\ExceptionHandler;
use Slim\Factory\AppFactory;

// Instantiate the app
$app = AppFactory::create();

// ...
// Create and register $errorHandler in error middleware

$request = Psr17ServerRequestCreatorFactoryImplementation::create();

$exceptionHandler = new ExceptionHandler($request, $errorHandler, $inDevelopment, true, true);
$exceptionHandler->registerHandling();

// ...

$app->run($request);

// This error will be captured and gracefully handled
trigger_error('This is embarrasing', \E_USER_ERROR);
```

## Upgrade from 1.x

* Minimum Slim version is now 4.0
* Slim 4 has migrated to Exception based error handling, basically what 1.x was doing already, so several parts of slim-exception have been dropped
* Global error/exception handling has been moved from a trait (meant for App) to its own class ExceptionHandler

## Contributing

Found a bug or have a feature request? [Please open a new issue](https://github.com/juliangut/slim-exception/issues). Have a look at existing issues before.

See file [CONTRIBUTING.md](https://github.com/juliangut/slim-exception/blob/master/CONTRIBUTING.md)

## License

See file [LICENSE](https://github.com/juliangut/slim-exception/blob/master/LICENSE) included with the source code for a copy of the license terms.
