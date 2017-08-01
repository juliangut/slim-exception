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
use Jgut\Slim\Exception\Handler\MethodNotAllowedHandler;
use Jgut\Slim\Exception\HttpExceptionFactory;
use PHPUnit\Framework\TestCase;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Not allowed handler tests.
 */
class MethodNotAllowedExceptionHandlerTest extends TestCase
{
    public function testJSONOutput()
    {
        /* @var MethodNotAllowedHandler $handler */
        $handler = $this->getMockBuilder(MethodNotAllowedHandler::class)
            ->setMethods(['isCli'])
            ->getMock();

        $request = Request::createFromEnvironment(Environment::mock(['HTTP_ACCEPT' => 'application/json']));

        /* @var Response $parsedResponse */
        $parsedResponse = $handler->handleException(
            $request,
            new Response(),
            HttpExceptionFactory::methodNotAllowed()
        );

        self::assertEquals(StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED, $parsedResponse->getStatusCode());
        self::assertEquals('application/json; charset=utf-8', $parsedResponse->getHeaderLine('Content-Type'));
        self::assertRegExp('/"ref":".+","message":"Method not allowed"/', (string) $parsedResponse->getBody());
    }

    public function testXMLOutput()
    {
        /* @var MethodNotAllowedHandler $handler */
        $handler = $this->getMockBuilder(MethodNotAllowedHandler::class)
            ->setMethods(['isCli'])
            ->getMock();

        $request = Request::createFromEnvironment(Environment::mock(['HTTP_ACCEPT' => 'application/xml']));

        /* @var Response $parsedResponse */
        $parsedResponse = $handler->handleException(
            $request,
            new Response(),
            HttpExceptionFactory::methodNotAllowed()
        );

        self::assertEquals(StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED, $parsedResponse->getStatusCode());
        self::assertEquals('application/xml; charset=utf-8', $parsedResponse->getHeaderLine('Content-Type'));
        self::assertRegExp('!<ref>.+</ref><message>Method not allowed</message>!', (string) $parsedResponse->getBody());
    }

    public function testHTMLOutput()
    {
        /* @var MethodNotAllowedHandler $handler */
        $handler = $this->getMockBuilder(MethodNotAllowedHandler::class)
            ->setMethods(['isCli'])
            ->getMock();

        $request = Request::createFromEnvironment(Environment::mock(['HTTP_ACCEPT' => 'text/html']));

        /* @var Response $parsedResponse */
        $parsedResponse = $handler->handleException(
            $request,
            new Response(),
            HttpExceptionFactory::methodNotAllowed()
        );

        self::assertEquals(StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED, $parsedResponse->getStatusCode());
        self::assertEquals('text/html; charset=utf-8', $parsedResponse->getHeaderLine('Content-Type'));
        self::assertRegExp('!<h1>Method not allowed \(Ref\. .+\)</h1>!', (string) $parsedResponse->getBody());
    }

    public function testTextOutput()
    {
        $handler = new MethodNotAllowedHandler();

        $request = Request::createFromEnvironment(Environment::mock(['HTTP_ACCEPT' => 'text/plain']));

        /* @var Response $parsedResponse */
        $parsedResponse = $handler->handleException(
            $request,
            new Response(),
            HttpExceptionFactory::methodNotAllowed()
        );

        self::assertEquals(StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED, $parsedResponse->getStatusCode());
        self::assertEquals('text/plain; charset=utf-8', $parsedResponse->getHeaderLine('Content-Type'));
        self::assertRegExp('/^\(.+\) Method not allowed$/', (string) $parsedResponse->getBody());
    }

    public function testDefaultOutput()
    {
        $handler = new MethodNotAllowedHandler();

        $request = Request::createFromEnvironment(Environment::mock(['HTTP_ACCEPT' => 'text/unknown']));

        /* @var Response $parsedResponse */
        $parsedResponse = $handler->handleException(
            $request,
            new Response(),
            HttpExceptionFactory::methodNotAllowed()
        );

        self::assertEquals(StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED, $parsedResponse->getStatusCode());
        self::assertEquals('text/plain; charset=utf-8', $parsedResponse->getHeaderLine('Content-Type'));
        self::assertRegExp('/^\(.+\) Method not allowed$/', (string) $parsedResponse->getBody());
    }
}
