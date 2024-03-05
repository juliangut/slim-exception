<?php

/*
 * (c) 2017-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-exception
 */

declare(strict_types=1);

namespace Jgut\Slim\Exception\Tests\Whoops\Renderer;

use ErrorException;
use Jgut\Slim\Exception\Whoops\Renderer\PlainTextRenderer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotImplementedException;
use Whoops\Exception\Inspector;
use Whoops\Run as Whoops;

/**
 * @internal
 */
class PlainTextRendererTest extends TestCase
{
    public function testOutput(): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $originalException = new ErrorException('Original exception');
        $exception = new HttpNotImplementedException($request, null, $originalException);
        $inspector = new Inspector($exception);

        $renderer = new PlainTextRenderer();
        $renderer->addTraceFunctionArgsToOutput(true);
        $renderer->setException($exception);
        $renderer->setInspector($inspector);
        $renderer->setRun(new Whoops());

        ob_start();
        $renderer->handle();
        $output = ob_get_clean();

        $file = __FILE__;

        $expected = <<<EXPECTED
        Type: Slim\\Exception\\HttpNotImplementedException
        Code: 501
        Message: 501 Not Implemented
        File: {$file}
        Line: 31
        Trace:
        EXPECTED;
        static::assertStringContainsString($expected, $output);
    }

    public function testNoTraceOutput(): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $exception = new HttpNotImplementedException($request);
        $inspector = new Inspector($exception);

        $renderer = new PlainTextRenderer();
        $renderer->addTraceToOutput(false);
        $renderer->setException($exception);
        $renderer->setInspector($inspector);
        $renderer->setRun(new Whoops());

        ob_start();
        $renderer->handle();
        $output = ob_get_clean();

        static::assertEquals('501 Not Implemented', $output);
    }
}
