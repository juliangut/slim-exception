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
use Negotiation\Negotiator;
use PHPUnit\Framework\TestCase;
use Slim\Exception\HttpBadRequestException;
use Slim\Interfaces\CallableResolverInterface;
use Whoops\Run as Whoops;
use Zend\Diactoros\ResponseFactory;
use Zend\Diactoros\ServerRequest;

/**
 * Whoops error handler tests.
 */
class ErrorHandlerTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /^Renderer ".+" for Whoops error handler does not implement .+/
     */
    public function testInvalidHandler(): void
    {
        $request = (new ServerRequest())
            ->withHeader('Accept', 'application/*+json');
        $exception = new HttpBadRequestException($request);

        $callableResolver = $this->getMockBuilder(CallableResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $callableResolver->expects($this->once())
            ->method('resolve')
            ->with(JsonRenderer::class)
            ->will($this->returnValue(function (): void {
                // noop
            }));
        /* @var CallableResolverInterface $callableResolver */
        $handler = new WhoopsErrorHandlerStub($callableResolver, new ResponseFactory(), new Negotiator(), new Whoops());

        $response = $handler($request, $exception, false, false, true);
    }

    public function testHandle(): void
    {
        $request = (new ServerRequest())
            ->withHeader('Accept', 'application/json');
        $exception = new HttpBadRequestException($request);

        $callableResolver = $this->getMockBuilder(CallableResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $callableResolver->expects($this->once())
            ->method('resolve')
            ->with(JsonRenderer::class)
            ->will($this->returnValue(new JsonRenderer()));
        /* @var CallableResolverInterface $callableResolver */
        $handler = new WhoopsErrorHandlerStub($callableResolver, new ResponseFactory(), new Negotiator(), new Whoops());

        $response = $handler($request, $exception, false, false, true);

        self::assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        self::assertContains('"type": "Slim\\\Exception\\\HttpBadRequestException"', (string) $response->getBody());
    }

    public function testDefaultHandle(): void
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
        $handler = new WhoopsErrorHandlerStub($callableResolver, new ResponseFactory(), new Negotiator(), new Whoops());

        $response = $handler($request, $exception, false, false, true);

        self::assertEquals('text/html', $response->getHeaderLine('Content-Type'));
        self::assertContains('Bad request', (string) $response->getBody());
    }
}
