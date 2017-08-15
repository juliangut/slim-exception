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
use Negotiation\Negotiator;
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
    /**
     * @var Negotiator
     */
    protected $negotiator;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->negotiator = new Negotiator();
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

        $manager = new HttpExceptionManager(new HandlerStub($this->negotiator));
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

        $manager = new HttpExceptionManager(new HandlerStub($this->negotiator));
        $manager->setLogger($logger);

        $request = Request::createFromEnvironment(Environment::mock());

        $originalException = new \ErrorException('Original error');

        $manager->handleHttpException(
            $request,
            new Response(),
            HttpExceptionFactory::internalServerError($exceptionMessage, null, null, $originalException)
        );
    }

    public function testErrorHandler()
    {
        $request = Request::createFromEnvironment(Environment::mock());

        $manager = new HttpExceptionManager(new HandlerStub($this->negotiator));

        /* @var Response $parsedResponse */
        $parsedResponse = $manager->errorHandler($request, new Response(), new \Exception('message', 0));

        self::assertEquals(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR, $parsedResponse->getStatusCode());
        self::assertEquals('Internal server error', (string) $parsedResponse->getBody());
    }

    public function testErrorHandlerByStatusCode()
    {
        $request = Request::createFromEnvironment(Environment::mock());

        $customHandler = $this->getMockBuilder(AbstractHttpExceptionHandler::class)
            ->setConstructorArgs([$this->negotiator])
            ->getMockForAbstractClass();
        $customHandler->expects($this->once())
            ->method('getContentTypes')
            ->will($this->returnValue(['text/plain']));
        $customHandler->expects($this->once())
            ->method('getExceptionOutput')
            ->will($this->returnValue('Captured exception'));
        /* @var HttpExceptionHandler $customHandler */

        $manager = new HttpExceptionManager(new HandlerStub($this->negotiator));
        $manager->addHandler(StatusCodeInterface::STATUS_BAD_REQUEST, $customHandler);

        /* @var Response $parsedResponse */
        $parsedResponse = $manager->errorHandler($request, new Response(), HttpExceptionFactory::badRequest());

        self::assertEquals(StatusCodeInterface::STATUS_BAD_REQUEST, $parsedResponse->getStatusCode());
        self::assertEquals('Captured exception', (string) $parsedResponse->getBody());
    }

    public function testNotFoundErrorHandler()
    {
        $request = Request::createFromEnvironment(Environment::mock());

        $manager = new HttpExceptionManager(new HandlerStub($this->negotiator));

        /* @var Response $parsedResponse */
        $parsedResponse = $manager->notFoundHandler($request, new Response());

        self::assertEquals(StatusCodeInterface::STATUS_NOT_FOUND, $parsedResponse->getStatusCode());
        self::assertEquals('Not found', (string) $parsedResponse->getBody());
    }

    public function testNotAllowedErrorHandler()
    {
        $request = Request::createFromEnvironment(Environment::mock());

        $manager = new HttpExceptionManager(new HandlerStub($this->negotiator));

        /* @var Response $parsedResponse */
        $parsedResponse = $manager->notAllowedHandler($request, new Response(), ['POST', 'PUT']);

        self::assertEquals(StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED, $parsedResponse->getStatusCode());
        self::assertEquals('Method "GET" not allowed. Must be one of POST, PUT', (string) $parsedResponse->getBody());
    }

    public function testNotAllowedRequestHandler()
    {
        $request = Request::createFromEnvironment(Environment::mock(['REQUEST_METHOD' => 'OPTIONS']));

        $manager = new HttpExceptionManager(new HandlerStub($this->negotiator));

        /* @var Response $parsedResponse */
        $parsedResponse = $manager->notAllowedHandler($request, new Response(), ['POST', 'PUT']);

        self::assertEquals(StatusCodeInterface::STATUS_OK, $parsedResponse->getStatusCode());
        self::assertEquals('Allowed methods: POST, PUT', (string) $parsedResponse->getBody());
    }
}
