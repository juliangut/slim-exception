<?php

/*
 * (c) 2017-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-exception
 */

declare(strict_types=1);

namespace Jgut\Slim\Exception\Tests\Whoops\Renderer;

use Jgut\Slim\Exception\Whoops\Renderer\JsonRenderer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpForbiddenException;
use Whoops\Exception\Inspector;
use Whoops\Run as Whoops;

/**
 * @internal
 */
class JsonRendererTest extends TestCase
{
    public function testOutput(): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $exception = new HttpForbiddenException($request, 'Forbidden');
        $inspector = new Inspector($exception);

        $renderer = new JsonRenderer();
        $renderer->setException($exception);
        $renderer->setInspector($inspector);
        $renderer->setRun(new Whoops());

        ob_start();
        $renderer->handle();
        $output = ob_get_clean();

        $file = __FILE__;

        $expected = <<<EXPECTED
        {
            "error": [
                {
                    "type": "Slim\\\\Exception\\\\HttpForbiddenException",
                    "message": "403 Forbidden",
                    "code": 403,
                    "file": "{$file}",
                    "line": 31,
                    "trace": [
                        {
        EXPECTED;
        static::assertStringContainsString($expected, $output);
    }

    public function testNotPrettifiedOutput(): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $exception = new HttpForbiddenException($request, 'Forbidden');
        $inspector = new Inspector($exception);

        $renderer = new JsonRenderer();
        $renderer->setException($exception);
        $renderer->setInspector($inspector);
        $renderer->setPrettify(false);
        $renderer->setRun(new Whoops());

        ob_start();
        $renderer->handle();
        $output = ob_get_clean();

        $expected = '{"error":[{'
            . '"type":"Slim\\\\Exception\\\\HttpForbiddenException",'
            . '"message":"403 Forbidden",'
            . '"code":403,'
            . '"file":"' . __FILE__ . '",'
            . '"line":63,'
            . '"trace":[{';
        static::assertStringContainsString($expected, $output);
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
        $renderer->setRun(new Whoops());

        ob_start();
        $renderer->handle();
        $output = ob_get_clean();

        $file = __FILE__;

        $expected = <<<EXPECTED
        {
            "error": [
                {
                    "type": "Slim\\\\Exception\\\\HttpForbiddenException",
                    "message": "403 Forbidden",
                    "code": 403,
                    "file": "{$file}",
                    "line": 89
                }
            ]
        }
        EXPECTED;
        static::assertEquals($expected, $output);
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
        $renderer->setRun(new Whoops());

        ob_start();
        $renderer->handle();
        $output = ob_get_clean();

        $expected = '{"error":[{'
            . '"type":"Slim\\\\Exception\\\\HttpForbiddenException",'
            . '"message":"403 Forbidden",'
            . '"code":403,'
            . '"file":"' . __FILE__ . '",'
            . '"line":123'
            . '}]}';
        static::assertEquals($expected, $output);
    }
}
