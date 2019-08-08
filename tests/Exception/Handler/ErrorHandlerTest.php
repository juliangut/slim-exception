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

use Jgut\Slim\Exception\Handler\ErrorHandler;
use Jgut\Slim\Exception\Renderer\HtmlRenderer;
use Jgut\Slim\Exception\Renderer\TextRenderer;
use Jgut\Slim\Exception\Tests\Stubs\ErrorHandlerStub;
use Negotiation\Negotiator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Interfaces\CallableResolverInterface;
use Zend\Diactoros\ResponseFactory;
use Zend\Diactoros\ServerRequest;

/**
 * Default error handler tests.
 */
class ErrorHandlerTest extends TestCase
{
    public function testHandle()
    {
        $request = new ServerRequest();
        $exception = new HttpBadRequestException($request);

        $callableResolver = $this->getMockBuilder(CallableResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $callableResolver->expects($this->once())
            ->method('resolve')
            ->with(TextRenderer::class)
            ->will($this->returnValue(new TextRenderer()));
        /* @var CallableResolverInterface $callableResolver */
        $handler = new ErrorHandler($callableResolver, new ResponseFactory(), new Negotiator());

        $response = $handler($request, $exception, true, false, true);

        self::assertEquals('text/plain', $response->getHeaderLine('Content-Type'));
        self::assertEquals('(0) Bad request.', (string) $response->getBody());
    }

    public function testDefaultHandle()
    {
        $request = new ServerRequest();
        $exception = new HttpBadRequestException($request);

        $callableResolver = $this->getMockBuilder(CallableResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $callableResolver->expects($this->once())
            ->method('resolve')
            ->with(HtmlRenderer::class)
            ->will($this->returnValue(new HtmlRenderer()));
        /* @var CallableResolverInterface $callableResolver */
        $handler = new ErrorHandlerStub($callableResolver, new ResponseFactory(), new Negotiator());

        $response = $handler($request, $exception, false, false, true);

        self::assertEquals('text/html', $response->getHeaderLine('Content-Type'));
        self::assertContains('An application error has occurred', (string) $response->getBody());
    }

    public function testLoggingError()
    {
        $exception = new \ErrorException('Custom error', 0, \E_USER_WARNING);

        $callableResolver = $this->getMockBuilder(CallableResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $callableResolver->expects($this->once())
            ->method('resolve')
            ->with(HtmlRenderer::class)
            ->will($this->returnValue(new HtmlRenderer()));
        /* @var CallableResolverInterface $callableResolver */
        $handler = new ErrorHandlerStub($callableResolver, new ResponseFactory(), new Negotiator());

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects($this->once())
            ->method('log');
        /* @var LoggerInterface $logger */
        $handler->setLogger($logger);

        $handler(new ServerRequest(), $exception, false, true, true);
    }

    public function testLoggingHttpError()
    {
        $request = new ServerRequest();
        $exception = new HttpBadRequestException($request);

        $callableResolver = $this->getMockBuilder(CallableResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $callableResolver->expects($this->once())
            ->method('resolve')
            ->with(HtmlRenderer::class)
            ->will($this->returnValue(new HtmlRenderer()));
        /* @var CallableResolverInterface $callableResolver */
        $handler = new ErrorHandlerStub($callableResolver, new ResponseFactory(), new Negotiator());

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects($this->once())
            ->method('log');
        /* @var LoggerInterface $logger */
        $handler->setLogger($logger);

        $handler($request, $exception, false, true, false);
    }
}
