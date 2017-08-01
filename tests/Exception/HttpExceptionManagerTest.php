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

namespace Jgut\Slim\Exception\Tests;

use Fig\Http\Message\StatusCodeInterface;
use Jgut\Slim\Exception\Handler\AbstractHttpExceptionHandler;
use Jgut\Slim\Exception\HttpExceptionFactory;
use Jgut\Slim\Exception\HttpExceptionHandler;
use Jgut\Slim\Exception\HttpExceptionManager;
use Jgut\Slim\Exception\Tests\Stubs\HandlerStub;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * HTTP exceptions manager tests.
 */
class HttpExceptionManagerTest extends TestCase
{
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

        $manager = new HttpExceptionManager(new HandlerStub());
        $manager->setLogger($logger);

        $request = Request::createFromEnvironment(Environment::mock());

        $manager->handleHttpException(
            $request,
            new Response(),
            HttpExceptionFactory::badRequest($exceptionMessage)
        );
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

        $manager = new HttpExceptionManager(new HandlerStub());
        $manager->setLogger($logger);

        $request = Request::createFromEnvironment(Environment::mock());

        $originalException = new \ErrorException('Original error');

        $manager->handleHttpException(
            $request,
            new Response(),
            HttpExceptionFactory::internalServerError($exceptionMessage, null, $originalException)
        );
    }

    public function testErrorHandler()
    {
        $request = Request::createFromEnvironment(Environment::mock());

        $manager = new HttpExceptionManager(new HandlerStub());

        $handler = $manager->getErrorHandler();

        $this->assertInstanceOf(\Closure::class, $handler);

        /* @var Response $parsedResponse */
        $parsedResponse = $handler($request, new Response(), new \Exception('message', 0));

        self::assertEquals(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR, $parsedResponse->getStatusCode());
        self::assertEquals('Internal server error', (string) $parsedResponse->getBody());
    }

    public function testErrorHandlerByStatusCode()
    {
        $request = Request::createFromEnvironment(Environment::mock());

        $customHandler = $this->getMockForAbstractClass(AbstractHttpExceptionHandler::class);
        $customHandler->expects($this->once())
            ->method('getExceptionOutput')
            ->will($this->returnValue('Captured exception'));
        /* @var HttpExceptionHandler $customHandler */

        $manager = new HttpExceptionManager(new HandlerStub());
        $manager->addHandler(StatusCodeInterface::STATUS_BAD_REQUEST, $customHandler);

        $handler = $manager->getErrorHandler();

        $this->assertInstanceOf(\Closure::class, $handler);

        /* @var Response $parsedResponse */
        $parsedResponse = $handler($request, new Response(), HttpExceptionFactory::badRequest());

        self::assertEquals(StatusCodeInterface::STATUS_BAD_REQUEST, $parsedResponse->getStatusCode());
        self::assertEquals('Captured exception', (string) $parsedResponse->getBody());
    }

    public function testNotFoundErrorHandler()
    {
        $request = Request::createFromEnvironment(Environment::mock());

        $manager = new HttpExceptionManager(new HandlerStub());

        $handler = $manager->getNotFoundHandler();

        $this->assertInstanceOf(\Closure::class, $handler);

        /* @var Response $parsedResponse */
        $parsedResponse = $handler($request, new Response());

        self::assertEquals(StatusCodeInterface::STATUS_NOT_FOUND, $parsedResponse->getStatusCode());
        self::assertEquals('Not found', (string) $parsedResponse->getBody());
    }

    public function testNotAllowedErrorHandler()
    {
        $request = Request::createFromEnvironment(Environment::mock());

        $manager = new HttpExceptionManager(new HandlerStub());

        $handler = $manager->getNotAllowedHandler();

        $this->assertInstanceOf(\Closure::class, $handler);

        /* @var Response $parsedResponse */
        $parsedResponse = $handler($request, new Response(), ['POST', 'PUT']);

        self::assertEquals(StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED, $parsedResponse->getStatusCode());
        self::assertEquals('Method "GET" not allowed. Must be one of POST, PUT', (string) $parsedResponse->getBody());
    }

    public function testNotAllowedRequestHandler()
    {
        $request = Request::createFromEnvironment(Environment::mock(['REQUEST_METHOD' => 'OPTIONS']));

        $manager = new HttpExceptionManager(new HandlerStub());

        $handler = $manager->getNotAllowedHandler();

        $this->assertInstanceOf(\Closure::class, $handler);

        /* @var Response $parsedResponse */
        $parsedResponse = $handler($request, new Response(), ['POST', 'PUT']);

        self::assertEquals(StatusCodeInterface::STATUS_OK, $parsedResponse->getStatusCode());
        self::assertEquals('Allowed methods: POST, PUT', (string) $parsedResponse->getBody());
    }
}
