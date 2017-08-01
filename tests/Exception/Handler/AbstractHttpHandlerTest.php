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
use Jgut\Slim\Exception\HttpExceptionFactory;
use Jgut\Slim\Exception\Tests\Stubs\HandlerStub;
use PHPUnit\Framework\TestCase;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Abstract HTTP exception handler tests.
 */
class AbstractHttpHandlerTest extends TestCase
{
    public function testCustomContentType()
    {
        $handler = new HandlerStub();

        $request = Request::createFromEnvironment(Environment::mock(['HTTP_ACCEPT' => 'text/plain']));

        /* @var Response $parsedResponse */
        $parsedResponse = $handler->handleException(
            $request,
            new Response(),
            HttpExceptionFactory::internalServerError()
        );

        self::assertEquals(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR, $parsedResponse->getStatusCode());
        self::assertEquals('Internal server error', (string) $parsedResponse->getBody());
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
        $handler = new HandlerStub();

        $request = Request::createFromEnvironment(Environment::mock(['HTTP_ACCEPT' => $contentType]));

        /* @var Response $parsedResponse */
        $parsedResponse = $handler->handleException(
            $request,
            new Response(),
            HttpExceptionFactory::internalServerError()
        );

        self::assertEquals(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR, $parsedResponse->getStatusCode());
        self::assertEquals('Internal server error', (string) $parsedResponse->getBody());
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
            ['text/html;q=0.6', 'text/html'],
            ['text/unknown', 'text/plain'],
        ];
    }
}
