<?php

/*
 * slim-exception (https://github.com/juliangut/slim-exception).
 * Slim HTTP exceptions and exception handling.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-exception
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

declare(strict_types=1);

namespace Jgut\Slim\Tests\Exception\Dumper\Whoops;

use Jgut\Slim\Exception\Handler\Whoops\ExceptionHandler;
use Jgut\Slim\Exception\HttpExceptionFactory;
use PHPUnit\Framework\TestCase;
use Slim\Http\Environment;
use Slim\Http\Request;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\XmlResponseHandler;
use Whoops\Run as Whoops;

/**
 * Whoops HTTP exception handler tests.
 */
class ExceptionHandlerTest extends TestCase
{
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /No content type defined for .+ handler/
     */
    public function testInvalidHandler()
    {
        $handler = $this->getMockBuilder(PlainTextHandler::class)
            ->getMock();
        $handler->expects(self::once())
            ->method('contentType')
            ->will($this->returnValue(null));
        /* @var PlainTextHandler $handler */

        $whoops = new Whoops();
        $whoops->pushHandler($handler);

        new ExceptionHandler($whoops);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /No content type defined for .+ handler/
     */
    public function testInvalidContentType()
    {
        $textHandler = $this->getMockBuilder(PlainTextHandler::class)
            ->getMock();
        /* @var PlainTextHandler $textHandler */

        $whoops = new Whoops();

        $handler = new ExceptionHandler($whoops);

        $handler->addHandler($textHandler, 10);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage There is no defined handler for content type "text/unknown"
     */
    public function testNotDefinedContentType()
    {
        $handler = new ExceptionHandler(new Whoops());

        $exception = HttpExceptionFactory::badRequest();
        $request = Request::createFromEnvironment(Environment::mock());

        $handler->getExceptionOutput('text/unknown', $exception, $request);
    }

    public function testJson()
    {
        $jsonHandler = $this->getMockBuilder(JsonResponseHandler::class)
            ->setMethodsExcept(['contentType'])
            ->getMock();
        $jsonHandler->expects(self::once())
            ->method('handle');
        /* @var JsonResponseHandler $jsonHandler */

        $handler = new ExceptionHandler(new Whoops());
        $handler->addHandler($jsonHandler);

        $exception = HttpExceptionFactory::forbidden();
        $request = Request::createFromEnvironment(Environment::mock());

        $handler->getExceptionOutput('application/json', $exception, $request);
    }

    public function testXml()
    {
        $xmlHandler = $this->getMockBuilder(XmlResponseHandler::class)
            ->setMethodsExcept(['contentType'])
            ->getMock();
        $xmlHandler->expects(self::once())
            ->method('handle');
        /* @var XmlResponseHandler $xmlHandler */

        $handler = new ExceptionHandler(new Whoops());
        $handler->addHandler($xmlHandler);

        $exception = HttpExceptionFactory::conflict();
        $request = Request::createFromEnvironment(Environment::mock());

        $handler->getExceptionOutput('application/xml', $exception, $request);
    }

    public function testHtml()
    {
        $htmlHandler = $this->getMockBuilder(PrettyPageHandler::class)
            ->setMethodsExcept(['contentType'])
            ->getMock();
        $htmlHandler->expects(self::once())
            ->method('handle');
        /* @var PrettyPageHandler $htmlHandler */

        $handler = new ExceptionHandler(new Whoops());
        $handler->addHandler($htmlHandler);

        $exception = HttpExceptionFactory::tooManyRequests();
        $request = Request::createFromEnvironment(Environment::mock());

        $handler->getExceptionOutput('text/html', $exception, $request);
    }

    public function testText()
    {
        $textHandler = $this->getMockBuilder(PlainTextHandler::class)
            ->setMethodsExcept(['contentType'])
            ->getMock();
        $textHandler->expects(self::once())
            ->method('handle');
        /* @var PlainTextHandler $textHandler */

        $handler = new ExceptionHandler(new Whoops());
        $handler->addHandler($textHandler);

        $exception = HttpExceptionFactory::badRequest();
        $request = Request::createFromEnvironment(Environment::mock());

        $handler->getExceptionOutput('text/plain', $exception, $request);
    }
}
