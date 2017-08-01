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

This package aims to unify error handling into a simpler and more extensible OOP API, handling exceptions depending on what HTTP status code they should produce.

## Installation

### Composer

```
composer require juliangut/slim-exception
```

## Usage

Require composer autoload file

```php
require './vendor/autoload.php';

use Jgut\Slim\Exception\Handler\ExceptionHandler;
use Jgut\Slim\Exception\Handler\MethodNotAllowedExceptionHandler;
use Jgut\Slim\Exception\Handler\NotFoundExceptionHandler;
use Jgut\Slim\Exception\HttpExceptionManager;

// Create Slim App

// Create manager with handlers for HTTP exceptions you want to capture
$exceptionManager = new HttpExceptionManager(new ExceptionHandler());
$exceptionManager->addHandler(404, new NotFoundHandler();
$exceptionManager->addHandler(405, new MethodNotAllowedHandler();
$exceptionManager->addHandler(400, new YourBadRequestHandler();
$exceptionManager->addHandler(401, new YourUnauthorizedRequestHandler();

$exceptionManager->setLogger(new Psr3LoggerInstance());

$container = $app->getContainer();

$container['errorHandler'] = $container['phpErrorHandler'] = $manager->getErrorHandler();
$container['notAllowedHandler'] = $manager->getNotAllowedHandler();
$container['notFoundHandler'] =  NotAllowedHandler->getNotFoundHandler();

// ...

$app->run();
```

Original Slim's error handling is bypassed by the HTTP exception manager forcing that any unhandled exception thrown during application execution will be ultimately transformed into an `HttpException` and handed over to `HttpExceptionManager::handleHttpException`

Non HttpExceptions (for example an exception thrown by a third party library) will be automatically transformed into a 500 HttpException and alternatively you can use `HttpExceptionFactory` to throw HTTP exceptions yourself. Those exceptions will be handled to their corresponding handler depending on their "status code" or by the default handler in case no handler is defined for the specific status code

In the above example if you `throw HttpExceptionFactory::unauthorized()` during the execution of the application it'll be captured by the manager had handed over to "YourUnauthorizedRequestHandler" so you can format and return a proper custom response

### HTTP exceptions

The base of this error handling are the HTTP exceptions. This exceptions carry an HTTP status code which is used by the manager to hand the exception to corresponding handler apart from being used as response status code

```php
use Jgut\Slim\Exception\HttpException;

$exceptionCode = 101; // Internal code
$httpStatusCode = 401; // Unauthorized
$exception = new HttpException('You shall not pass!', $exceptionCode, $httpStatusCode);

$exception->getHttpStatusCode(); // 401 Unauthorized
```

Additionally exceptions have a unique identifier which can be used for logging exceptions and displaying for example on APIs, allowing you to have more information over the erroneous situation when addressed

```php
$exception->getIdentifier();
```

#### Factory

In order to simplify HTTP exception creation and assure correct HTTP status code selection there are several shortcut creation methods

```php
throw HttpExceptionFactory::unauthorized('You shall not pass!', 101);
throw HttpExceptionFactory::notAcceptable('Throughput reached', 102);
throw HttpExceptionFactory::unprocessableEntity('Already exists', 103);
``` 

### Handlers

HttpExceptionManager hands control to handlers based on the status code of the exception being treated

Only one default handler is mandatory (set on manager construction). This default manager will be responsible of handling exceptions which don't have an specific associated handler. This handler serves the same purpose as Slim's 'errorHandler' and 'phpErrorHandler'

Out of the box three handlers are provided

* `ExceptionHandler` meant to be used as default fallback handler (any unhandled errors)
* `NotFoundHandler` meant for 404 errors
* `MethodNotAllowedHandler` meant for 405 errors

#### Custom handlers

By implementing `HttpExceptionHandler` interface (or extending `AbstractHttpExceptionHandler`) you can create your custom exception handlers and assign them to the status code you want

```php
use Jgut\Slim\Exception\Handler\AbstractHttpExceptionHandler;

class MyCustomHandler extends AbstractHttpExceptionHandler
{
    public function handleException(
        RequestInterface $request,
        ResponseInterface $response,
        HttpException $exception
    ): ResponseInterface {
        // return response formatted in the fashion you please
    }
}
``` 

```php
use Jgut\Slim\Exception\Handler\ExceptionHandler;
use Jgut\Slim\Exception\HttpExceptionManager;

$exceptionManager = new HttpExceptionManager(new ExceptionHandler());
$exceptionManager->addHandler([400, 401, 403, 406, 409], new MyCustomHandler();
``` 

### Whoops

Development environment deserves a better more informative error handling.

[Whoops](https://github.com/filp/whoops) is a great tool for this purpose and its usage is contemplated by this package. There is an special Whoops HTTP exception handler which can be used as default exception handler

For you to use this handler you'll need to require whoops first

```
composer require filp/whoops
```

```php
use Jgut\Slim\Exception\Handler\Whoops\ExceptionHandler;
use Jgut\Slim\Exception\Handler\Whoops\HtmlHandler;
use Jgut\Slim\Exception\Handler\Whoops\JsonHandler;
use Jgut\Slim\Exception\Handler\Whoops\TextHandler;
use Jgut\Slim\Exception\Handler\Whoops\XmlHandler;
use Jgut\Slim\Exception\HttpExceptionManager;
use Whoops\Run;

$whoopsHandler = new ExceptionHandler(new Run());

// Assign whoops handlers per content type
$whoopsHandler->addHandler(new HtmlHandler(), ['text/html', 'application/xhtml+xml']);
$whoopsHandler->addHandler(new JsonHandler(), ['text/json', 'application/json', 'application/x-json']);
$whoopsHandler->addHandler(new XmlHandler(), ['text/xml', 'application/xml', 'application/x-xml']);
$whoopsHandler->addHandler(new TextHandler(), ['text/plain']);

$exceptionManager = new HttpExceptionManager($whoopsHandler);
```

## Catch all errors

In order to fully integrate error handling with the environment you can extend Slim's App to use HttpExceptionAwareTrait. In this way any unhandled error will be captured and treated by the error handler (including fatal errors)

```php
use Jgut\Slim\Exception\HttpExceptionAwareTrait;
use Slim\App as SlimApp; 

class App extends SlimApp
{
    use HttpExceptionAwareTrait;

    public function __construct($container = [])
    {
        parent::__construct($container);

        $this->registerErrorHandling();
    }
}
```

## Contributing

Found a bug or have a feature request? [Please open a new issue](https://github.com/juliangut/slim-exception/issues). Have a look at existing issues before.

See file [CONTRIBUTING.md](https://github.com/juliangut/slim-exception/blob/master/CONTRIBUTING.md)

## License

See file [LICENSE](https://github.com/juliangut/slim-exception/blob/master/LICENSE) included with the source code for a copy of the license terms.
