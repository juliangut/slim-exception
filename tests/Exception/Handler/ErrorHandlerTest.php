<?php

/*
 * (c) 2017-2025 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-exception
 */

declare(strict_types=1);

namespace Jgut\Slim\Exception\Tests\Handler;

use ErrorException;
use Jgut\Slim\Exception\Handler\ErrorHandler;
use Jgut\Slim\Exception\Renderer\HtmlRenderer;
use Jgut\Slim\Exception\Renderer\PlainTextRenderer;
use Jgut\Slim\Exception\Tests\Stubs\ErrorHandlerStub;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequest;
use Negotiation\Negotiator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Interfaces\CallableResolverInterface;

/**
 * @internal
 */
class ErrorHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $request = new ServerRequest();
        $exception = new HttpBadRequestException($request);

        $callableResolver = $this->getMockBuilder(CallableResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $callableResolver->expects(static::once())
            ->method('resolve')
            ->with(PlainTextRenderer::class)
            ->willReturn(new PlainTextRenderer());
        $handler = new ErrorHandler($callableResolver, new ResponseFactory(), new Negotiator());
        $handler->setErrorRenderers(['text/plain' => PlainTextRenderer::class]);

        $response = $handler($request, $exception, false, false, true);

        static::assertEquals('text/plain', $response->getHeaderLine('Content-Type'));
        static::assertEquals('400 Bad Request', (string) $response->getBody());
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
        $handler = new ErrorHandlerStub($callableResolver, new ResponseFactory(), new Negotiator());

        $response = $handler($request, $exception, false, false, true);

        static::assertEquals('text/html', $response->getHeaderLine('Content-Type'));
        static::assertStringContainsString('<h1>400 Bad Request</h1>', (string) $response->getBody());
    }

    public function testLoggingError(): void
    {
        $exception = new ErrorException('Custom error', 0, \E_USER_WARNING);

        $callableResolver = $this->getMockBuilder(CallableResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $callableResolver->expects(static::any())
            ->method('resolve')
            ->willReturnCallback(static function ($class) {
                return match (true) {
                    $class === PlainTextRenderer::class => new PlainTextRenderer(),
                    $class === HtmlRenderer::class => new HtmlRenderer(),
                };
            })
            ->willReturnOnConsecutiveCalls(new PlainTextRenderer(), new HtmlRenderer());

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(static::once())
            ->method('log');

        $handler = new ErrorHandlerStub($callableResolver, new ResponseFactory(), new Negotiator(), $logger);

        $handler(new ServerRequest(), $exception, false, true, true);
    }

    public function testLoggingHttpError(): void
    {
        $request = new ServerRequest();
        $exception = new HttpBadRequestException($request);

        $callableResolver = $this->getMockBuilder(CallableResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $callableResolver->expects(static::any())
            ->method('resolve')
            ->willReturnCallback(static function ($class) {
                return match (true) {
                    $class === PlainTextRenderer::class => new PlainTextRenderer(),
                    $class === HtmlRenderer::class => new HtmlRenderer(),
                };
            })
            ->willReturnOnConsecutiveCalls(new PlainTextRenderer(), new HtmlRenderer());

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(static::once())
            ->method('log');

        $handler = new ErrorHandlerStub($callableResolver, new ResponseFactory(), new Negotiator(), $logger);

        $handler($request, $exception, false, true, false);
    }
}
