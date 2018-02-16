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
use Jgut\HttpException\BadRequestHttpException;
use Jgut\HttpException\ForbiddenHttpException;
use Jgut\HttpException\InternalServerErrorHttpException;
use Jgut\HttpException\MethodNotAllowedHttpException;
use Jgut\HttpException\NotFoundHttpException;
use Jgut\HttpException\TooManyRequestsHttpException;
use Jgut\HttpException\UnauthorizedHttpException;
use Jgut\Slim\Exception\ExceptionManager;
use Jgut\Slim\Exception\Formatter\Text;
use Jgut\Slim\Exception\Handler\ExceptionHandler;
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
class ExceptionManagerTest extends TestCase
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

        $formatter = $this->getMockBuilder(Text::class)
            ->getMock();
        /* @var Text $formatter */

        $handler = new HandlerStub($this->negotiator);
        $handler->addFormatter($formatter, 'text/plain'); // Because it's being tested on CLI

        $manager = new ExceptionManager($handler);
        $manager->setLogger($logger);

        $request = Request::createFromEnvironment(Environment::mock());

        $manager->handleHttpException(
            $request,
            new Response(),
            new BadRequestHttpException($exceptionMessage)
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

        $formatter = $this->getMockBuilder(Text::class)
            ->getMock();
        /* @var Text $formatter */

        $handler = new HandlerStub($this->negotiator);
        $handler->addFormatter($formatter, 'text/plain'); // Because it's being tested on CLI

        $manager = new ExceptionManager($handler);
        $manager->setLogger($logger);

        $request = Request::createFromEnvironment(Environment::mock());

        $originalException = new \ErrorException('Original error');

        $manager->handleHttpException(
            $request,
            new Response(),
            new InternalServerErrorHttpException($exceptionMessage, null, null, $originalException)
        );
    }

    public function testDefaultErrorHandler()
    {
        $request = Request::createFromEnvironment(Environment::mock());

        $formatter = $this->getMockBuilder(Text::class)
            ->getMock();
        /* @var Text $formatter */

        $handler = new HandlerStub($this->negotiator);
        $handler->addFormatter($formatter, 'text/plain'); // Because it's being tested on CLI

        $manager = new ExceptionManager($handler);

        /* @var Response $parsedResponse */
        $parsedResponse = $manager->errorHandler($request, new Response(), new \Exception('message', 0));

        self::assertEquals(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR, $parsedResponse->getStatusCode());
        self::assertEquals('Internal Server Error', (string) $parsedResponse->getBody());
    }

    public function testAddUnauthorizedHandler()
    {
        $request = Request::createFromEnvironment(Environment::mock());

        $formatter = $this->getMockBuilder(Text::class)
            ->getMock();
        /* @var Text $formatter */

        $handler = new HandlerStub($this->negotiator);
        $handler->addFormatter($formatter, 'text/plain'); // Because it's being tested on CLI

        $manager = new ExceptionManager(new ExceptionHandler($this->negotiator));
        $manager->addUnauthorizedHandler($handler);

        /* @var Response $parsedResponse */
        $parsedResponse = $manager->errorHandler($request, new Response(), new UnauthorizedHttpException());

        self::assertEquals(StatusCodeInterface::STATUS_UNAUTHORIZED, $parsedResponse->getStatusCode());
    }

    public function testAddForbiddenHandler()
    {
        $request = Request::createFromEnvironment(Environment::mock());

        $formatter = $this->getMockBuilder(Text::class)
            ->getMock();
        /* @var Text $formatter */

        $handler = new HandlerStub($this->negotiator);
        $handler->addFormatter($formatter, 'text/plain'); // Because it's being tested on CLI

        $manager = new ExceptionManager(new ExceptionHandler($this->negotiator));
        $manager->addForbiddenHandler($handler);

        /* @var Response $parsedResponse */
        $parsedResponse = $manager->errorHandler($request, new Response(), new ForbiddenHttpException());

        self::assertEquals(StatusCodeInterface::STATUS_FORBIDDEN, $parsedResponse->getStatusCode());
    }

    public function testAddNotFoundHandler()
    {
        $request = Request::createFromEnvironment(Environment::mock());

        $formatter = $this->getMockBuilder(Text::class)
            ->getMock();
        /* @var Text $formatter */

        $handler = new HandlerStub($this->negotiator);
        $handler->addFormatter($formatter, 'text/plain'); // Because it's being tested on CLI

        $manager = new ExceptionManager(new ExceptionHandler($this->negotiator));
        $manager->addNotFoundHandler($handler);

        /* @var Response $parsedResponse */
        $parsedResponse = $manager->errorHandler($request, new Response(), new NotFoundHttpException());

        self::assertEquals(StatusCodeInterface::STATUS_NOT_FOUND, $parsedResponse->getStatusCode());
    }

    public function testAddMethodNotAllowedHandler()
    {
        $request = Request::createFromEnvironment(Environment::mock());

        $formatter = $this->getMockBuilder(Text::class)
            ->getMock();
        /* @var Text $formatter */

        $handler = new HandlerStub($this->negotiator);
        $handler->addFormatter($formatter, 'text/plain'); // Because it's being tested on CLI

        $manager = new ExceptionManager(new ExceptionHandler($this->negotiator));
        $manager->addMethodNotAllowedHandler($handler);

        /* @var Response $parsedResponse */
        $parsedResponse = $manager->errorHandler($request, new Response(), new MethodNotAllowedHttpException());

        self::assertEquals(StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED, $parsedResponse->getStatusCode());
    }

    public function testCustomErrorHandler()
    {
        $request = Request::createFromEnvironment(Environment::mock());

        $formatter = $this->getMockBuilder(Text::class)
            ->getMock();
        /* @var Text $formatter */

        $handler = new HandlerStub($this->negotiator);
        $handler->addFormatter($formatter, 'text/plain'); // Because it's being tested on CLI

        $manager = new ExceptionManager(new ExceptionHandler($this->negotiator));
        $manager->addHandler(TooManyRequestsHttpException::class, $handler);

        /* @var Response $parsedResponse */
        $parsedResponse = $manager->errorHandler($request, new Response(), new TooManyRequestsHttpException());

        self::assertEquals(StatusCodeInterface::STATUS_TOO_MANY_REQUESTS, $parsedResponse->getStatusCode());
    }

    public function testNotFoundErrorHandler()
    {
        $request = Request::createFromEnvironment(Environment::mock());

        $formatter = $this->getMockBuilder(Text::class)
            ->getMock();
        /* @var Text $formatter */

        $handler = new HandlerStub($this->negotiator);
        $handler->addFormatter($formatter, 'text/plain'); // Because it's being tested on CLI

        $manager = new ExceptionManager($handler);

        /* @var Response $parsedResponse */
        $parsedResponse = $manager->notFoundHandler($request, new Response());

        self::assertEquals(StatusCodeInterface::STATUS_NOT_FOUND, $parsedResponse->getStatusCode());
        self::assertEquals('Not Found', (string) $parsedResponse->getBody());
    }

    public function testOptionsNotFoundErrorHandler()
    {
        $request = Request::createFromEnvironment(Environment::mock(['REQUEST_METHOD' => 'OPTIONS']));

        $manager = new ExceptionManager(new HandlerStub($this->negotiator));

        /* @var Response $parsedResponse */
        $parsedResponse = $manager->notFoundHandler($request, new Response());

        self::assertEquals(StatusCodeInterface::STATUS_OK, $parsedResponse->getStatusCode());
        self::assertEquals('text/plain; charset=utf-8', $parsedResponse->getHeaderLine('Content-Type'));
        self::assertEquals('Not Found', (string) $parsedResponse->getBody());
    }

    public function testNotAllowedErrorHandler()
    {
        $request = Request::createFromEnvironment(Environment::mock());

        $formatter = $this->getMockBuilder(Text::class)
            ->getMock();
        /* @var Text $formatter */

        $handler = new HandlerStub($this->negotiator);
        $handler->addFormatter($formatter, 'text/plain'); // Because it's being tested on CLI

        $manager = new ExceptionManager($handler);

        /* @var Response $parsedResponse */
        $parsedResponse = $manager->notAllowedHandler($request, new Response(), ['POST', 'PUT']);

        self::assertEquals(StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED, $parsedResponse->getStatusCode());
        self::assertEquals('Method GET not allowed. Must be one of: POST, PUT', (string) $parsedResponse->getBody());
    }

    public function testOptionsNotAllowedErrorHandler()
    {
        $request = Request::createFromEnvironment(Environment::mock(['REQUEST_METHOD' => 'OPTIONS']));

        $manager = new ExceptionManager(new HandlerStub($this->negotiator));

        /* @var Response $parsedResponse */
        $parsedResponse = $manager->notAllowedHandler($request, new Response(), ['POST', 'PUT']);

        self::assertEquals(StatusCodeInterface::STATUS_OK, $parsedResponse->getStatusCode());
        self::assertEquals('text/plain; charset=utf-8', $parsedResponse->getHeaderLine('Content-Type'));
        self::assertEquals('Allowed methods: POST, PUT', (string) $parsedResponse->getBody());
    }
}
