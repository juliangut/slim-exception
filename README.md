[![PHP version](https://img.shields.io/badge/PHP-%3E%3D7-8892BF.svg?style=flat-square)](http://php.net)
[![Latest Version](https://img.shields.io/packagist/v/juliangut/slim-exception.svg?style=flat-square)](https://packagist.org/packages/juliangut/slim-exception)
[![License](https://img.shields.io/github/license/juliangut/slim-exception.svg?style=flat-square)](https://github.com/juliangut/slim-exception/blob/master/LICENSE)

[![Build Status](https://img.shields.io/travis/juliangut/slim-exception.svg?style=flat-square)](https://travis-ci.org/juliangut/slim-exception)
[![Style Check](https://styleci.io/repos/98827578/shield)](https://styleci.io/repos/98827578)
[![Code Quality](https://img.shields.io/scrutinizer/g/juliangut/slim-exception.svg?style=flat-square)](https://scrutinizer-ci.com/g/juliangut/slim-exception)
[![Code Coverage](https://img.shields.io/coveralls/juliangut/slim-exception.svg?style=flat-square)](https://coveralls.io/github/juliangut/slim-exception)

[![Total Downloads](https://img.shields.io/packagist/dt/juliangut/slim-exception.svg?style=flat-square)](https://packagist.org/packages/juliangut/slim-exception/stats)
[![Monthly Downloads](https://img.shields.io/packagist/dm/juliangut/slim-exception.svg?style=flat-square)](https://packagist.org/packages/juliangut/slim-exception/stats)

# slim-exception

HTTP aware exceptions and exception handling for Slim Framework

Default Slim's error handlers customization of exceptions response is cumbersome to say the least, error handlers are quite simple, doesn't really provide any useful information during development and at the same time are ugly when on production.

This package aims to unify error handling into a simpler and more extensible OOP API by providing an HTTP aware exception class and handling those depending on what HTTP status code they should produce.

## Installation

### Composer

```
composer require juliangut/slim-exception
```

## Usage

Require composer autoload file

```php
require './vendor/autoload.php';

use Fig\Http\Message\StatusCodeInterface;
use Jgut\Slim\Exception\Formatter\Html;
use Jgut\Slim\Exception\Formatter\Json;
use Jgut\Slim\Exception\Handler\ExceptionHandler;
use Jgut\Slim\Exception\HttpExceptionManager;
use Negotiation\Negotiator;

// Create Slim App

$contentNegotiator = new Negotiator();

// Create default exception handler
$defaultHandler = new ExceptionHandler($contentNegotiator);
$defaultHandler->addFormatter(new Json());
$defaultHandler->addFormatter(new Html());

// Create manager with default handler
$exceptionManager = new HttpExceptionManager($defaultHandler);

// Add handler for 404 "Not found" HTTP exceptions
$notFoundHandler = new ExceptionHandler($contentNegotiator);
$notFoundHandler->addFormatter(new Json());
$notFoundHandler->addFormatter(new Html('Not found', 'The requested page could not be found'));
$exceptionManager->addHandler(StatusCodeInterface::STATUS_NOT_FOUND, $notFoundHandler);

// Add handler for 405 "Method not allowed" HTTP exceptions
$notAllowedHandler = new ExceptionHandler($contentNegotiator);
$notAllowedHandler->addFormatter(new Json());
$notAllowedHandler->addFormatter(new Html('Method not allowed', 'The requested method is not allowed'));
$exceptionManager->addHandler(StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED, $notAllowedHandler);

$exceptionManager->setLogger(new Psr3LoggerInstance());

$container = $app->getContainer();

$container['errorHandler'] = $container['phpErrorHandler'] = [$manager, 'errorHandler'];
$container['notFoundHandler'] =  [$manager, 'notFoundHandler'];
$container['notAllowedHandler'] = [$manager, 'notAllowedHandler'];

// ...

$app->run();
```

Original Slim's error handling is bypassed by the HTTP exception manager forcing that any unhandled exception thrown during application execution will be ultimately transformed into an `HttpException` and handed over to `HttpExceptionManager::handleHttpException`

Non HttpExceptions (for example an exception thrown by a third party library) will be automatically transformed into a 500 HttpException, alternatively you can use `HttpExceptionFactory` to throw HTTP exceptions yourself. Those exceptions will be handled to their corresponding handler depending on their "status code" or by the default handler in case no handler is defined for the specific status code

In the above example if you `throw HttpExceptionFactory::unauthorized()` during the execution of the application it'll be captured by the manager hand handed over to the default handler due to no handler has been specified for HTTP error 401

### HTTP exceptions

The base of this error handling are the HTTP exceptions. This exceptions carry an HTTP status code which is used by the manager to hand the exception to corresponding handler apart from being used as response status code

```php
use Jgut\Slim\Exception\HttpException;

$exceptionMessage = 'You shall not pass!';
$exceptionDescription = 'You do not have permission';
$exceptionCode = 1001; // Internal code
$httpStatusCode = 401; // Unauthorized
$exception = new HttpException($exceptionMessage, $exceptionDescription, $exceptionCode, $httpStatusCode);

$exception->getStatusCode(); // 401 Unauthorized
```

Additionally exceptions have a description and a unique identifier which can be used for logging exceptions and displaying for example on APIs, allowing you to have more information over the erroneous situation when addressed

```php
$exception->getDescription();
$exception->getIdentifier();
```

#### Factory

In order to simplify HTTP exception creation and assure correct HTTP status code selection there are several shortcut creation methods

```php
throw HttpExceptionFactory::unauthorized('You shall not pass!', 'You do not have permission', 1001);
throw HttpExceptionFactory::notAcceptable('Throughput reached', 'Too much', 1002);
throw HttpExceptionFactory::unprocessableEntity('Already exists', 'Entity already exists', 1030);
``` 

### Handlers

HttpExceptionManager hands control to handlers based on the status code of the exception being treated

Only one default handler is mandatory (set on manager construction). This default manager will be responsible of handling exceptions which don't have an specific associated handler. This handler serves the same purpose as Slim's 'errorHandler' and 'phpErrorHandler'

Out of the box two handlers are provided

* `Jgut\Slim\Exception\Handler\ExceptionHandler` easily extendable handler for production
* `Jgut\Slim\Exception\Whoops\Handler\ExceptionHandler` meant for development only

#### Custom handlers

By implementing `HttpExceptionHandler` interface (or extending `Jgut\Slim\Exception\Handler\ExceptionHandler`) you can create your custom exception handlers and assign them to the status code you want

```php
use Jgut\Slim\Exception\Handler\ExceptionHandler;

class MyCustomHandler extends ExceptionHandler
{
    // handle and respond a formatted exception in the fashion you please
}
``` 

```php
use Jgut\Slim\Exception\Handler\ExceptionHandler;
use Jgut\Slim\Exception\HttpExceptionManager;
use Negotiation\Negotiator;

$defaultHandler = new ExceptionHandler(new Negotiator());
// Add exception formatters

$customHandler = new MyCustomHandler(new Negotiator());
// Add exception formatters

$exceptionManager = new HttpExceptionManager($defaultHandler);
$exceptionManager->addHandler([400, 401, 403, 406, 409], new MyCustomHandler();
``` 

### Formatters

Formatting HTTP exceptions is performed based on "Accept" header of the request.

There are four types of formatters bundled in the library but you can create your own easily

* `Html` responds to text/html and application/xhtml+xml content types
* `Json` responds to application/json, text/json and application/x-json content types
* `Text` responds to text/plain content type
* `Xml` responds to application/xml, text/xml and application/x-xml content types

### Whoops

Development environment deserves a better, more informative error handling.

[Whoops](https://github.com/filp/whoops) is a great tool for this purpose and its usage is contemplated by this package. There is an special Whoops HTTP exception handler which can be used as default exception handler for development

**_Consider including Whoops and var-dumper in production as well to benefit from better and richer stack traces on logs_**

For you to use this handler you'll need to require whoops first. Additionally symfony's var-dumper plays nice with whoops so require it too

```
composer require filp/whoops
composer require symfony/var-dumper
```

```php
use Jgut\Slim\Exception\Whoops\Formatter\Html;
use Jgut\Slim\Exception\Whoops\Formatter\Json;
use Jgut\Slim\Exception\Whoops\Handler\ExceptionHandler as WhoopsExceptionHandler;
use Jgut\Slim\Exception\HttpExceptionManager;
use Negotiation\Negotiator;
use Whoops\Run;

// Create Whoops handler and assign formatters
$whoopsHandler = new WhoopsExceptionHandler(new Negotiator(), new Run());
$whoopsHandler->addFormatter(new Html());
$whoopsHandler->addFormatter(new Json());

// Set Whoops handler as default for development
$exceptionManager = new HttpExceptionManager($whoopsHandler);
```

## Handle all errors

In order to fully integrate error handling with the environment you can extend Slim's App to use HttpExceptionAwareTrait. In this way any triggered and unhandled error will be captured and treated by the error handler

```php
use Jgut\Slim\Exception\HttpExceptionAwareTrait;
use Slim\App as SlimApp; 

class App extends SlimApp
{
    use HttpExceptionAwareTrait;

    public function __construct($container = [])
    {
        parent::__construct($container);

        $this->registerPhpErrorHandling();
    }
}

$app = new App();

// This error will be captured and handled gracefully
trigger_error('This is embarrasing', E_USER_ERROR);
```

## Contributing

Found a bug or have a feature request? [Please open a new issue](https://github.com/juliangut/slim-exception/issues). Have a look at existing issues before.

See file [CONTRIBUTING.md](https://github.com/juliangut/slim-exception/blob/master/CONTRIBUTING.md)

## License

See file [LICENSE](https://github.com/juliangut/slim-exception/blob/master/LICENSE) included with the source code for a copy of the license terms.
