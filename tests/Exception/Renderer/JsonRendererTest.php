<?php

/*
 * (c) 2017-2025 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-exception
 */

declare(strict_types=1);

namespace Jgut\Slim\Exception\Tests\Renderer;

use Jgut\Slim\Exception\Renderer\JsonRenderer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpForbiddenException;

/**
 * @internal
 */
class JsonRendererTest extends TestCase
{
    public function testOutput(): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $exception = new HttpForbiddenException($request, 'Forbidden action');
        $output = (new JsonRenderer())($exception, false);

        $expected = <<<'EXPECTED'
        {
            "error": {
                "message": "403 Forbidden"
            }
        }
        EXPECTED;
        static::assertEquals($expected, $output);
    }

    public function testNotPrettifiedOutput(): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $exception = new HttpForbiddenException($request, 'Forbidden action');
        $renderer = new JsonRenderer();
        $renderer->setPrettify(false);

        $output = $renderer($exception, false);

        static::assertEquals('{"error":{"message":"403 Forbidden"}}', $output);
    }

    public function testOutputWithTrace(): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $exception = new HttpForbiddenException($request, 'Forbidden action');
        $output = (new JsonRenderer())($exception, true);

        $expected = <<<'EXPECTED'
        {
            "error": {
                "message": "403 Forbidden",
                "exception": [

        EXPECTED;
        static::assertStringContainsString($expected, $output);
    }

    public function testNotPrettifiedOutputWithTrace(): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $exception = new HttpForbiddenException($request, 'Forbidden action');
        $renderer = new JsonRenderer();
        $renderer->setPrettify(false);

        $output = $renderer($exception, true);

        static::assertStringContainsString('{"error":{"message":"403 Forbidden","exception":[', $output);
    }
}
