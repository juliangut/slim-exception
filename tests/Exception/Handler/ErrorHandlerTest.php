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
use Jgut\Slim\Exception\Handler\ErrorHandler;
use PHPUnit\Framework\TestCase;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Default errors handler tests.
 */
class ErrorHandlerTest extends TestCase
{
    public function testTextOutput()
    {
        $handler = new ErrorHandler();

        $request = Request::createFromEnvironment(Environment::mock());

        /* @var Response $parsedResponse */
        $parsedResponse = $handler($request, new Response(), new \Exception());

        self::assertContains('Application error', (string) $parsedResponse->getBody());
    }

    public function testJSONOutput()
    {
        /* @var ErrorHandler $handler */
        $handler = $this->getMockBuilder(ErrorHandler::class)
            ->setMethods(['isCli'])
            ->getMock();

        $request = Request::createFromEnvironment(Environment::mock(['HTTP_ACCEPT' => 'application/json']));

        /* @var Response $parsedResponse */
        $parsedResponse = $handler($request, new Response(), new \Exception());

        self::assertEquals(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR, $parsedResponse->getStatusCode());
        self::assertEquals('application/json; charset=utf-8', $parsedResponse->getHeaderLine('Content-Type'));
        self::assertRegExp('/"ref":".+","message":"Application error"/', (string) $parsedResponse->getBody());
    }

    public function testXMLOutput()
    {
        /* @var ErrorHandler $handler */
        $handler = $this->getMockBuilder(ErrorHandler::class)
            ->setMethods(['isCli'])
            ->getMock();

        $request = Request::createFromEnvironment(Environment::mock(['HTTP_ACCEPT' => 'application/xml']));

        /* @var Response $parsedResponse */
        $parsedResponse = $handler($request, new Response(), new \Exception());

        self::assertEquals(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR, $parsedResponse->getStatusCode());
        self::assertEquals('application/xml; charset=utf-8', $parsedResponse->getHeaderLine('Content-Type'));
        self::assertRegExp('!<ref>.+</ref><message>Application error</message>!', (string) $parsedResponse->getBody());
    }

    public function testHTMLOutput()
    {
        /* @var ErrorHandler $handler */
        $handler = $this->getMockBuilder(ErrorHandler::class)
            ->setMethods(['isCli'])
            ->getMock();

        $request = Request::createFromEnvironment(Environment::mock(['HTTP_ACCEPT' => 'text/html']));

        /* @var Response $parsedResponse */
        $parsedResponse = $handler($request, new Response(), new \Exception());

        self::assertEquals(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR, $parsedResponse->getStatusCode());
        self::assertEquals('text/html; charset=utf-8', $parsedResponse->getHeaderLine('Content-Type'));
        self::assertRegExp('!<h1>Application error \(Ref\. .+\)</h1>!', (string) $parsedResponse->getBody());
    }
}
