[![PHP version](https://img.shields.io/badge/PHP-%3E%3D8.0-8892BF.svg?style=flat-square)](http://php.net)
[![Latest Version](https://img.shields.io/packagist/v/juliangut/slim-exception.svg?style=flat-square)](https://packagist.org/packages/juliangut/slim-exception)
[![License](https://img.shields.io/github/license/juliangut/slim-exception.svg?style=flat-square)](https://github.com/juliangut/slim-exception/blob/master/LICENSE)

[![Total Downloads](https://img.shields.io/packagist/dt/juliangut/slim-exception.svg?style=flat-square)](https://packagist.org/packages/juliangut/slim-exception/stats)
[![Monthly Downloads](https://img.shields.io/packagist/dm/juliangut/slim-exception.svg?style=flat-square)](https://packagist.org/packages/juliangut/slim-exception/stats)

# slim-exception

Alternative Slim error handling with better response format negotiation, better exception logging and better development support

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
$logger = new Logger();

$errorHandler = $inDevelopment && class_exists(WhoopsErrorHandler::class)
    ? new WhoopsErrorHandler($callableResolver, $responseFactory, new Negotiator(), $logger)
    : new ErrorHandler($callableResolver, $responseFactory, new Negotiator(), $logger);

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware($inDevelopment, true, true);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

// ...

$app->run();
```

### Renderers

Custom error renderers are configured when using slim-exception error handlers. Fear not, out of the box ErrorHandler is a direct drop-in to change default Slim ErrorHandler

You can register your error renderers or completely change them

```php
$errorHandler = new ErrorHandler($callableResolver, $responseFactory, new Negotiator());

// Set single error renderer
$errorHandler->setErrorRenderer('application/xhtml+xml', MyCustomHtmlRenderer::class);

// Completely replace error renderers
$errorHandler->setErrorRenderers(['text/html' => MyCustomHtmlRenderer::class]);
``` 

### Whoops

Developers deserve a better and more informative error handling while in development environment

[Whoops](https://github.com/filp/whoops) is a great tool for this purpose and its usage is integrated in this package. There is a special Whoops error handler which can be used as default exception handler for development

Given Whoops renderers are meant for development displayErrorDetails argument on `Slim\Interfaces\ErrorRendererInterface::__invoke` won't be considered and stacktrace will always be displayed

The example of how to include Whoops error handler is in the code above

For you to use this handler you'll need to require whoops first. Additionally, Symfony's var-dumper plays nice with whoops so require it too

```
composer require --dev filp/whoops
composer require --dev symfony/var-dumper
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

$request = Psr17ServerRequestFactoryInterface::createServerRequest();

$exceptionHandler = new ExceptionHandler($request, $errorHandler, $inDevelopment, true, true);
$exceptionHandler->registerHandling();

// ...

$app->run($request);

// This error will be captured and gracefully handled
trigger_error('This is embarrassing', \E_USER_ERROR);
```

## Upgrade from 2.x

* Minimum PHP version is now 8.0
* Minimum Whoops version is now 2.15 as custom Inspector has been removed in favor of Whoop's frame filters

## Contributing

Found a bug or have a feature request? [Please open a new issue](https://github.com/juliangut/slim-exception/issues). Have a look at existing issues before.

See file [CONTRIBUTING.md](https://github.com/juliangut/slim-exception/blob/master/CONTRIBUTING.md)

## License

See file [LICENSE](https://github.com/juliangut/slim-exception/blob/master/LICENSE) included with the source code for a copy of the license terms.
