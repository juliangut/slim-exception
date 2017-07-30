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
use Jgut\Slim\Exception\Handler\NotAllowedHandler;
use PHPUnit\Framework\TestCase;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Not allowed handler tests.
 */
class NotAllowedHandlerTest extends TestCase
{
    public function testOptionsOutput()
    {
        /* @var NotAllowedHandler $handler */
        $handler = $this->getMockBuilder(NotAllowedHandler::class)
            ->setMethods(['isCli'])
            ->getMock();

        $request = Request::createFromEnvironment(Environment::mock(['REQUEST_METHOD' => 'OPTIONS']));

        /* @var Response $parsedResponse */
        $parsedResponse = $handler($request, new Response(), ['POST', 'PUT']);

        self::assertEquals(StatusCodeInterface::STATUS_OK, $parsedResponse->getStatusCode());
        self::assertEquals('text/plain; charset=utf-8', $parsedResponse->getHeaderLine('Content-Type'));
        self::assertContains('Allowed methods: POST, PUT', (string) $parsedResponse->getBody());
    }

    public function testTextOutput()
    {
        $handler = new NotAllowedHandler();

        $request = Request::createFromEnvironment(Environment::mock());

        /* @var Response $parsedResponse */
        $parsedResponse = $handler($request, new Response(), ['POST', 'PUT']);

        self::assertRegExp(
            '/^\(.+\) Method not allowed. Must be one of POST, PUT$/',
            (string) $parsedResponse->getBody()
        );
    }

    public function testJSONOutput()
    {
        /* @var NotAllowedHandler $handler */
        $handler = $this->getMockBuilder(NotAllowedHandler::class)
            ->setMethods(['isCli'])
            ->getMock();

        $request = Request::createFromEnvironment(Environment::mock(['HTTP_ACCEPT' => 'application/json']));

        /* @var Response $parsedResponse */
        $parsedResponse = $handler($request, new Response(), ['POST', 'PUT']);

        self::assertEquals(StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED, $parsedResponse->getStatusCode());
        self::assertEquals('application/json; charset=utf-8', $parsedResponse->getHeaderLine('Content-Type'));
        self::assertRegExp(
            '/"ref":".+","message":"Method not allowed\. Must be one of POST, PUT"/',
            (string) $parsedResponse->getBody()
        );
    }

    public function testXMLOutput()
    {
        /* @var NotAllowedHandler $handler */
        $handler = $this->getMockBuilder(NotAllowedHandler::class)
            ->setMethods(['isCli'])
            ->getMock();

        $request = Request::createFromEnvironment(Environment::mock(['HTTP_ACCEPT' => 'application/xml']));

        /* @var Response $parsedResponse */
        $parsedResponse = $handler($request, new Response(), ['POST']);

        self::assertEquals(StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED, $parsedResponse->getStatusCode());
        self::assertEquals('application/xml; charset=utf-8', $parsedResponse->getHeaderLine('Content-Type'));
        self::assertRegExp(
            '!<ref>.+</ref><message>Method not allowed\. Must be POST</message>!',
            (string) $parsedResponse->getBody()
        );
    }

    public function testHTMLOutput()
    {
        /* @var NotAllowedHandler $handler */
        $handler = $this->getMockBuilder(NotAllowedHandler::class)
            ->setMethods(['isCli'])
            ->getMock();

        $request = Request::createFromEnvironment(Environment::mock(['HTTP_ACCEPT' => 'text/html']));

        /* @var Response $parsedResponse */
        $parsedResponse = $handler($request, new Response(), ['POST', 'PUT']);

        self::assertEquals(StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED, $parsedResponse->getStatusCode());
        self::assertEquals('text/html; charset=utf-8', $parsedResponse->getHeaderLine('Content-Type'));
        self::assertRegExp('!<h1>Method not allowed \(Ref\. .+\)</h1>!', (string) $parsedResponse->getBody());
    }
}
