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
use Jgut\Slim\Exception\Handler\NotFoundHandler;
use PHPUnit\Framework\TestCase;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Not found handler tests.
 */
class NotFoundHandlerTest extends TestCase
{
    public function testTextOutput()
    {
        $handler = new NotFoundHandler();

        $request = Request::createFromEnvironment(Environment::mock());

        /* @var Response $parsedResponse */
        $parsedResponse = $handler($request, new Response());

        self::assertEquals(StatusCodeInterface::STATUS_NOT_FOUND, $parsedResponse->getStatusCode());
        self::assertEquals('text/plain; charset=utf-8', $parsedResponse->getHeaderLine('Content-Type'));
        self::assertRegExp('/^\(.+\) Not found$/', (string) $parsedResponse->getBody());
    }

    public function testJSONOutput()
    {
        /* @var NotFoundHandler $handler */
        $handler = $this->getMockBuilder(NotFoundHandler::class)
            ->setMethods(['isCli'])
            ->getMock();

        $request = Request::createFromEnvironment(Environment::mock(['HTTP_ACCEPT' => 'application/json']));

        /* @var Response $parsedResponse */
        $parsedResponse = $handler($request, new Response());

        self::assertEquals(StatusCodeInterface::STATUS_NOT_FOUND, $parsedResponse->getStatusCode());
        self::assertEquals('application/json; charset=utf-8', $parsedResponse->getHeaderLine('Content-Type'));
        self::assertRegExp('/"ref":".+","message":"Not found"/', (string) $parsedResponse->getBody());
    }

    public function testXMLOutput()
    {
        /* @var NotFoundHandler $handler */
        $handler = $this->getMockBuilder(NotFoundHandler::class)
            ->setMethods(['isCli'])
            ->getMock();

        $request = Request::createFromEnvironment(Environment::mock(['HTTP_ACCEPT' => 'application/xml']));

        /* @var Response $parsedResponse */
        $parsedResponse = $handler($request, new Response());

        self::assertEquals(StatusCodeInterface::STATUS_NOT_FOUND, $parsedResponse->getStatusCode());
        self::assertEquals('application/xml; charset=utf-8', $parsedResponse->getHeaderLine('Content-Type'));
        self::assertRegExp('!<ref>.+</ref><message>Not found</message>!', (string) $parsedResponse->getBody());
    }

    public function testHTMLOutput()
    {
        /* @var NotFoundHandler $handler */
        $handler = $this->getMockBuilder(NotFoundHandler::class)
            ->setMethods(['isCli'])
            ->getMock();

        $request = Request::createFromEnvironment(Environment::mock(['HTTP_ACCEPT' => 'text/html']));

        /* @var Response $parsedResponse */
        $parsedResponse = $handler($request, new Response());

        self::assertEquals(StatusCodeInterface::STATUS_NOT_FOUND, $parsedResponse->getStatusCode());
        self::assertEquals('text/html; charset=utf-8', $parsedResponse->getHeaderLine('Content-Type'));
        self::assertRegExp('!<h1>Not found \(Ref\. .+\)</h1>!', (string) $parsedResponse->getBody());
    }
}
