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
use Jgut\HttpException\InternalServerErrorHttpException;
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
            new InternalServerErrorHttpException(
                $exceptionMessage,
                '',
                StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR,
                $originalException
            )
        );
    }

    public function testErrorHandler()
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

    public function testErrorHandlerByHandler()
    {
        $request = Request::createFromEnvironment(Environment::mock());

        $formatter = $this->getMockBuilder(Text::class)
            ->getMock();
        /* @var Text $formatter */

        $handler = new HandlerStub($this->negotiator);
        $handler->addFormatter($formatter, 'text/plain'); // Because it's being tested on CLI

        $manager = new ExceptionManager(new ExceptionHandler($this->negotiator));
        $manager->addHandler(BadRequestHttpException::class, $handler);

        /* @var Response $parsedResponse */
        $parsedResponse = $manager->errorHandler($request, new Response(), new BadRequestHttpException());

        self::assertEquals(StatusCodeInterface::STATUS_BAD_REQUEST, $parsedResponse->getStatusCode());
        self::assertEquals('Bad Request', (string) $parsedResponse->getBody());
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
