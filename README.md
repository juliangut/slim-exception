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

HTTP aware exceptions and exception handling for Slim Framework

Default Slim's error handlers customization of exceptions response is cumbersome to say the least, error handlers are quite simple, doesn't really provide any useful information during development and at the same time are ugly when on production.

This package aims to unify error handling into a simpler and more extensible OOP API by providing an HTTP aware exception handling mechanism.

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
use Jgut\HttpException\MethodNotAllowedHttpException;
use Jgut\HttpException\NotFoundHttpException;
use Jgut\Slim\Exception\Formatter\Html;
use Jgut\Slim\Exception\Formatter\Json;
use Jgut\Slim\Exception\Handler\ExceptionHandler;
use Jgut\Slim\Exception\ExceptionManager;
use Negotiation\Negotiator;
use Slim\App;

$app = new App();

$contentNegotiator = new Negotiator();

// Create default exception handler
$defaultHandler = new ExceptionHandler($contentNegotiator);
$defaultHandler->addFormatter(new Json());
$defaultHandler->addFormatter(new Html());

// Create manager with default handler
$exceptionManager = new ExceptionManager($defaultHandler);
$exceptionManager->setLogger(new \Psr3LoggerInstance());

// Add handler for 404 "Not found" HTTP exceptions
$notFoundHandler = new ExceptionHandler($contentNegotiator);
$notFoundHandler->addFormatter(new Json());
$notFoundHandler->addFormatter(new Html('Not found', 'The requested page could not be found'));
$exceptionManager->addHandler(NotFoundHttpException::class, $notFoundHandler);

// Add handler for 405 "Method not allowed" HTTP exceptions
$notAllowedHandler = new ExceptionHandler($contentNegotiator);
$notAllowedHandler->addFormatter(new Json());
$notAllowedHandler->addFormatter(new Html('Method not allowed', 'The requested method is not allowed'));
$exceptionManager->addHandler(MethodNotAllowedHttpException::class, $notAllowedHandler);

$container = $app->getContainer();

$container['errorHandler'] = $container['phpErrorHandler'] = [$exceptionManager, 'errorHandler'];
$container['notFoundHandler'] =  [$exceptionManager, 'notFoundHandler'];
$container['notAllowedHandler'] = [$exceptionManager, 'notAllowedHandler'];

// ...

$app->run();
```

Original Slim's error handling is bypassed by the HTTP exception manager forcing that any unhandled exception thrown during application execution will be ultimately transformed into an `\Jgut\HttpException\InternalServerErrorHttpException` and handed over to `ExceptionManager::handleHttpException`

Non HttpExceptions (for example an exception thrown by a third party library) will be automatically transformed into a 500 InternalServerErrorHttpException, alternatively you can throw HTTP exceptions yourself. Exceptions will be handled by associated handler or by default handler in case no handler is defined for the specific status code

In the above example if you `throw new \Jgut\HttpException\UnauthorizedHttpException()` during the execution of the application it'll be captured by the manager hand handed over to the default handler due to no handler has been specified for that type of HTTP exception

### Handlers

ExceptionManager hands control to handlers based on the HTTP exception mapping

Only one default handler is mandatory (set on manager construction). This default manager will be responsible of handling exceptions which don't have an specific associated handler. This handler serves the same purpose as Slim's 'errorHandler' and 'phpErrorHandler'

Out of the box two handlers are provided

* `Jgut\Slim\Exception\Handler\ExceptionHandler` easily extendable handler for production
* `Jgut\Slim\Exception\Whoops\Handler\ExceptionHandler` meant for development only

#### Custom handlers

By implementing `ExceptionHandler` interface (or extending `Jgut\Slim\Exception\Handler\ExceptionHandler`) you can create your custom exception handlers and assign them to the HTTP exception you want

```php
use Jgut\Slim\Exception\Handler\ExceptionHandler;

class MyCustomHandler extends ExceptionHandler
{
    // handle and respond a formatted exception in the fashion you please
}
``` 

```php
use Jgut\HttpException\MethodNotAllowedHttpException;
use Jgut\HttpException\NotFoundHttpException;
use Jgut\Slim\Exception\Handler\ExceptionHandler;
use Jgut\Slim\Exception\ExceptionManager;
use Negotiation\Negotiator;

$defaultHandler = new ExceptionHandler(new Negotiator());
// Add exception formatters

$customHandler = new MyCustomHandler(new Negotiator());
// Add exception formatters

$exceptionManager = new ExceptionManager($defaultHandler);
$exceptionManager->addHandler(
    [
        BadRequestHttpException::class,
        UnauthorizedHttpException::class,
        MethodNotAllowedHttpException::class,
        NotAcceptableHttpException::class,
        ConflictHttpException::class
    ], 
    new MyCustomHandler()
);
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

$container = $app->getContainer();

$container['errorHandler'] = $container['phpErrorHandler'] = [$exceptionManager, 'errorHandler'];
$container['notFoundHandler'] =  [$exceptionManager, 'notFoundHandler'];
$container['notAllowedHandler'] = [$exceptionManager, 'notAllowedHandler'];
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
