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

namespace Jgut\Slim\Exception\Tests\Handler;

use Fig\Http\Message\StatusCodeInterface;
use Jgut\Slim\Exception\Dumper\Whoops\ExceptionDumper;
use Jgut\Slim\Exception\Handler\ErrorHandler;
use Jgut\Slim\Exception\HttpExceptionFactory;
use Jgut\Slim\Exception\Tests\Stubs\HandlerStub;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Abstract HTTP exception handler tests.
 */
class AbstractHttpHandlerTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage text/unknown is not among known content types
     */
    public function testInvalidDefaultContentType()
    {
        $handler = new ErrorHandler();
        $handler->setDefaultContentType('text/unknown');
    }

    public function testLogHttpException()
    {
        $exceptionMessage = 'This is the exception message';

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger
            ->expects(self::once())
            ->method('log')
            ->with(LogLevel::ERROR, $exceptionMessage);
        /* @var LoggerInterface $logger */

        $handler = new HandlerStub();
        $handler->setLogger($logger);

        $request = Request::createFromEnvironment(Environment::mock());

        $handler($request, new Response(), HttpExceptionFactory::badRequest($exceptionMessage));
    }

    public function testLogErrorException()
    {
        $exceptionMessage = 'This is the exception message';

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger
            ->expects(self::once())
            ->method('log')
            ->with(LogLevel::ALERT, $exceptionMessage);
        /* @var LoggerInterface $logger */

        $handler = new HandlerStub();
        $handler->setLogger($logger);

        $request = Request::createFromEnvironment(Environment::mock());

        $originalException = new \ErrorException('Original error');

        $handler(
            $request,
            new Response(),
            HttpExceptionFactory::internalServerError($exceptionMessage, null, $originalException)
        );
    }

    public function testCustomContentType()
    {
        $dumper = $this->getMockBuilder(ExceptionDumper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dumper
            ->expects(self::once())
            ->method('getFormattedException')
            ->will(self::throwException(new \RuntimeException()));
        /* @var ExceptionDumper $dumper */

        $handler = new ErrorHandler();
        $handler->setDumper($dumper);
        $handler->addContentType('text/unknown');
        $handler->setDefaultContentType('text/unknown');

        $request = Request::createFromEnvironment(Environment::mock());

        /* @var Response $parsedResponse */
        $parsedResponse = $handler($request, new Response(), HttpExceptionFactory::internalServerError());

        self::assertEquals(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR, $parsedResponse->getStatusCode());
        self::assertEquals('text/plain; charset=utf-8', $parsedResponse->getHeaderLine('Content-Type'));
    }

    /**
     * @dataProvider getFormatSpecifications
     *
     * @param string $contentType
     * @param string $expectedContentType
     * @param string $dumperMethod
     */
    public function testFormattedOutput($contentType, $expectedContentType)
    {
        $dumper = $this->getMockBuilder(ExceptionDumper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dumper
            ->expects(self::once())
            ->method('getFormattedException')
            ->will(self::returnValue('output'));
        /* @var ExceptionDumper $dumper */

        $handler = new HandlerStub();
        $handler->setDumper($dumper);

        $request = Request::createFromEnvironment(Environment::mock(['HTTP_ACCEPT' => $contentType]));

        /* @var Response $parsedResponse */
        $parsedResponse = $handler($request, new Response(), HttpExceptionFactory::internalServerError());

        self::assertEquals(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR, $parsedResponse->getStatusCode());
        self::assertEquals($expectedContentType . '; charset=utf-8', $parsedResponse->getHeaderLine('Content-Type'));
    }

    /**
     * Get output formats.
     *
     * @return array
     */
    public function getFormatSpecifications(): array
    {
        return [
            ['text/json', 'text/json'],
            ['application/json', 'application/json'],
            ['application/x-json', 'application/x-json'],
            ['text/xml', 'text/xml'],
            ['application/xml', 'application/xml'],
            ['application/x-xml', 'application/x-xml'],
            ['text/html', 'text/html'],
            ['application/xhtml+xml', 'application/xhtml+xml'],
            ['text/plain', 'text/plain'],
            ['text/unknown', 'text/html'],
        ];
    }
}
