<?php

/*
 * (c) 2017-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-exception
 */

declare(strict_types=1);

namespace Jgut\Slim\Exception\Tests\Renderer;

use Jgut\Slim\Exception\Renderer\PlainTextRenderer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpForbiddenException;

/**
 * @internal
 */
class PlainTextRendererTest extends TestCase
{
    protected HttpForbiddenException $exception;

    protected PlainTextRenderer $renderer;

    public function testOutput(): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $exception = new HttpForbiddenException($request, 'Forbidden action');
        $renderer = new PlainTextRenderer();

        $output = $renderer($exception, false);

        static::assertEquals('403 Forbidden', $output);
    }

    public function testOutputWithTrace(): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $exception = new HttpForbiddenException($request, 'Forbidden action');
        $renderer = new PlainTextRenderer();

        $output = $renderer($exception, true);

        $file = __FILE__;

        $expected /** @lang text */
            = <<<EXPECTED
            403 Forbidden
            Type: Slim\\Exception\\HttpForbiddenException
            Code: 403
            Message: Forbidden action
            File: {$file}
            Line: 44
            Trace:
            EXPECTED;

        static::assertStringContainsString($expected, $output);
    }
}
