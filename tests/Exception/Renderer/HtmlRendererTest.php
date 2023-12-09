<?php

/*
 * (c) 2017-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-exception
 */

declare(strict_types=1);

namespace Jgut\Slim\Exception\Tests\Renderer;

use Jgut\Slim\Exception\Renderer\HtmlRenderer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Slim\Exception\HttpForbiddenException;

/**
 * @internal
 */
class HtmlRendererTest extends TestCase
{
    public function testDefaultHttpExceptionOutput(): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $renderer = new HtmlRenderer();
        $exception = new HttpForbiddenException($request);
        $output = ($renderer)($exception, false);

        $expected /** @lang html */
            = <<<'EXPECTED'
            <!doctype html>
            <html lang="en">
                <head>
                    <meta charset="utf-8">
                    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

                    <title>403 Forbidden</title>

                    <style>
                        body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana,sans-serif}
                        h1{margin:0;font-size:48px;font-weight:normal;line-height:48px}
                        strong{display:inline-block;width:65px}
                    </style>
                </head>
                <body>
                    <h1>403 Forbidden</h1>
                    <div><p>Forbidden.</p></div>
                    <a href="#" onClick="window.history.go(-1)">Go Back</a>
                </body>
            </html>
            EXPECTED;
        static::assertEquals($expected, $output);
    }

    public function testMessageHttpExceptionOutput(): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $renderer = new HtmlRenderer();
        $exception = new HttpForbiddenException($request, 'No access');
        $output = ($renderer)($exception, false);

        $expected /** @lang html */
            = <<<'EXPECTED'
            <!doctype html>
            <html lang="en">
                <head>
                    <meta charset="utf-8">
                    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

                    <title>403 Forbidden</title>

                    <style>
                        body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana,sans-serif}
                        h1{margin:0;font-size:48px;font-weight:normal;line-height:48px}
                        strong{display:inline-block;width:65px}
                    </style>
                </head>
                <body>
                    <h1>403 Forbidden</h1>
                    <div><p>No access</p></div>
                    <a href="#" onClick="window.history.go(-1)">Go Back</a>
                </body>
            </html>
            EXPECTED;
        static::assertEquals($expected, $output);
    }

    public function testDescriptionHttpExceptionOutput(): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $renderer = new HtmlRenderer();
        $exception = new HttpForbiddenException($request, '');
        $output = ($renderer)($exception, false);

        $expected /** @lang html */
            = <<<'EXPECTED'
            <!doctype html>
            <html lang="en">
                <head>
                    <meta charset="utf-8">
                    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

                    <title>403 Forbidden</title>

                    <style>
                        body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana,sans-serif}
                        h1{margin:0;font-size:48px;font-weight:normal;line-height:48px}
                        strong{display:inline-block;width:65px}
                    </style>
                </head>
                <body>
                    <h1>403 Forbidden</h1>
                    <div><p>You are not permitted to perform the requested operation.</p></div>
                    <a href="#" onClick="window.history.go(-1)">Go Back</a>
                </body>
            </html>
            EXPECTED;
        static::assertEquals($expected, $output);
    }

    public function testOutputWithTrace(): void
    {
        $renderer = new HtmlRenderer();
        $exception = new RuntimeException();
        $output = ($renderer)($exception, true);

        $file = __FILE__;

        $expected /** @lang html */
            = <<<EXPECTED
            <!doctype html>
            <html lang="en">
                <head>
                    <meta charset="utf-8">
                    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

                    <title>Slim Application error</title>

                    <style>
                        body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana,sans-serif}
                        h1{margin:0;font-size:48px;font-weight:normal;line-height:48px}
                        strong{display:inline-block;width:65px}
                    </style>
                </head>
                <body>
                    <h1>Slim Application error</h1>
                    <div><p>The application could not run because of the following error:</p>
            <h2>Details</h2>
            <div><strong>Type:</strong> RuntimeException</div>
            <div><strong>Code:</strong> 0</div>
            <div><strong>Message:</strong> </div>
            <div><strong>File:</strong> {$file}</div>
            <div><strong>Line:</strong> 129</div>
            <h2>Trace</h2>
            EXPECTED;
        static::assertStringContainsString($expected, $output);
    }
}
