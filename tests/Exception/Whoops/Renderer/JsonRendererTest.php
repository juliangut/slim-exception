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

namespace Jgut\Slim\Exception\Tests\Whoops\Renderer;

use Jgut\Slim\Exception\Whoops\Renderer\JsonRenderer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpForbiddenException;
use Whoops\Exception\Inspector;

/**
 * Whoops custom JSON exception renderer tests.
 */
class JsonRendererTest extends TestCase
{
    public function testOutput(): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $exception = new HttpForbiddenException($request, 'Forbidden');
        $inspector = new Inspector($exception);

        $renderer = new JsonRenderer();
        $renderer->addTraceToOutput(true);
        $renderer->setException($exception);
        $renderer->setInspector($inspector);

        \ob_start();
        $renderer->handle();
        $output = \ob_get_clean();

        $expected = <<<'EXPECTED'
{
    "message": "403 Forbidden",
    "type": "Slim\\Exception\\HttpForbiddenException",
    "trace": [

EXPECTED;
        self::assertContains($expected, $output);
    }

    public function testNotPrettifiedOutput(): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $exception = new HttpForbiddenException($request, 'Forbidden');
        $inspector = new Inspector($exception);

        $renderer = new JsonRenderer();
        $renderer->addTraceToOutput(true);
        $renderer->setException($exception);
        $renderer->setInspector($inspector);
        $renderer->setPrettify(false);

        \ob_start();
        $renderer->handle();
        $output = \ob_get_clean();

        $expected = '{"message":"403 Forbidden","type":"Slim\\\Exception\\\HttpForbiddenException","trace":[';
        self::assertContains($expected, $output);
    }

    public function testNoTraceOutput(): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $exception = new HttpForbiddenException($request, 'Forbidden');
        $inspector = new Inspector($exception);

        $renderer = new JsonRenderer();
        $renderer->addTraceToOutput(false);
        $renderer->setException($exception);
        $renderer->setInspector($inspector);

        \ob_start();
        $renderer->handle();
        $output = \ob_get_clean();

        $expected = <<<'EXPECTED'
{
    "message": "403 Forbidden"
}
EXPECTED;
        self::assertEquals($expected, $output);
    }

    public function testNotPrettifiedNoTraceOutput(): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $exception = new HttpForbiddenException($request, 'Forbidden');
        $inspector = new Inspector($exception);

        $renderer = new JsonRenderer();
        $renderer->addTraceToOutput(false);
        $renderer->setException($exception);
        $renderer->setInspector($inspector);
        $renderer->setPrettify(false);

        \ob_start();
        $renderer->handle();
        $output = \ob_get_clean();

        $expected = '{"message":"403 Forbidden"}';
        self::assertEquals($expected, $output);
    }
}
