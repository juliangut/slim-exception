<?php

/*
 * slim-exception (https://github.com/juliangut/slim-exception).
 * Slim exception handling.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-exception
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

declare(strict_types=1);

namespace Jgut\Slim\Exception\Tests\Whoops\Handler;

use Jgut\Slim\Exception\Tests\Stubs\WhoopsErrorHandlerStub;
use Jgut\Slim\Exception\Whoops\Renderer\HtmlRenderer;
use Jgut\Slim\Exception\Whoops\Renderer\JsonRenderer;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequest;
use Negotiation\Negotiator;
use PHPUnit\Framework\TestCase;
use Slim\Exception\HttpBadRequestException;
use Slim\Interfaces\CallableResolverInterface;
use Whoops\Run as Whoops;

/**
 * Whoops error handler tests.
 */
class ErrorHandlerTest extends TestCase
{
    public function testInvalidHandler(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/^Renderer ".+" for Whoops error handler should implement ".+"\.$/');

        $request = (new ServerRequest())
            ->withHeader('Accept', 'application/*+json');
        $exception = new HttpBadRequestException($request);

        $callableResolver = $this->getMockBuilder(CallableResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $callableResolver->expects(static::once())
            ->method('resolve')
            ->with(JsonRenderer::class)
            ->willReturn(static function (): void {
                // noop
            });
        $handler = new WhoopsErrorHandlerStub($callableResolver, new ResponseFactory(), new Negotiator(), new Whoops());

        $handler($request, $exception, false, false, true);
    }

    public function testHandle(): void
    {
        $request = (new ServerRequest())
            ->withHeader('Accept', 'application/json');
        $exception = new HttpBadRequestException($request);

        $callableResolver = $this->getMockBuilder(CallableResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $callableResolver->expects(static::once())
            ->method('resolve')
            ->with(JsonRenderer::class)
            ->willReturn(new JsonRenderer());
        $handler = new WhoopsErrorHandlerStub($callableResolver, new ResponseFactory(), new Negotiator(), new Whoops());

        $response = $handler($request, $exception, false, false, true);

        static::assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        static::assertStringContainsString(
            '"type": "Slim\\\Exception\\\HttpBadRequestException"',
            (string) $response->getBody()
        );
    }

    public function testDefaultHandle(): void
    {
        $request = new ServerRequest();
        $exception = new HttpBadRequestException($request);

        $callableResolver = $this->getMockBuilder(CallableResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $callableResolver->expects(static::once())
            ->method('resolve')
            ->with(HtmlRenderer::class)
            ->willReturn(new HtmlRenderer());
        $handler = new WhoopsErrorHandlerStub($callableResolver, new ResponseFactory(), new Negotiator(), new Whoops());

        $response = $handler($request, $exception, false, false, true);

        static::assertEquals('text/html', $response->getHeaderLine('Content-Type'));
        static::assertStringContainsString('Bad request', (string) $response->getBody());
    }
}
