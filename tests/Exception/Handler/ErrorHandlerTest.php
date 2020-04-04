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

namespace Jgut\Slim\Exception\Tests\Handler;

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
 * Default error handler tests.
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
            ->will($this->returnValue(new PlainTextRenderer()));
        /* @var CallableResolverInterface $callableResolver */
        $handler = new ErrorHandler($callableResolver, new ResponseFactory(), new Negotiator());
        $handler->setErrorRenderers(['text/plain' => PlainTextRenderer::class]);

        $response = $handler($request, $exception, false, false, true);

        self::assertEquals('text/plain', $response->getHeaderLine('Content-Type'));
        self::assertEquals('400 Bad Request', (string) $response->getBody());
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
            ->will($this->returnValue(new HtmlRenderer()));
        /* @var CallableResolverInterface $callableResolver */
        $handler = new ErrorHandlerStub($callableResolver, new ResponseFactory(), new Negotiator());

        $response = $handler($request, $exception, false, false, true);

        self::assertEquals('text/html', $response->getHeaderLine('Content-Type'));
        self::assertContains('<h1>400 Bad Request</h1>', (string) $response->getBody());
    }

    public function testLoggingError(): void
    {
        $exception = new \ErrorException('Custom error', 0, \E_USER_WARNING);

        $callableResolver = $this->getMockBuilder(CallableResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $callableResolver->expects(static::any())
            ->method('resolve')
            ->withConsecutive([PlainTextRenderer::class], [HtmlRenderer::class])
            ->willReturnOnConsecutiveCalls(new PlainTextRenderer(), new HtmlRenderer());
        /* @var CallableResolverInterface $callableResolver */
        $handler = new ErrorHandlerStub($callableResolver, new ResponseFactory(), new Negotiator());

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(static::once())
            ->method('log');
        /* @var LoggerInterface $logger */
        $handler->setLogger($logger);

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
            ->withConsecutive([PlainTextRenderer::class], [HtmlRenderer::class])
            ->willReturnOnConsecutiveCalls(new PlainTextRenderer(), new HtmlRenderer());
        /* @var CallableResolverInterface $callableResolver */
        $handler = new ErrorHandlerStub($callableResolver, new ResponseFactory(), new Negotiator());

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(static::once())
            ->method('log');
        /* @var LoggerInterface $logger */
        $handler->setLogger($logger);

        $handler($request, $exception, false, true, false);
    }
}
