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

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $this->exception = new HttpForbiddenException($request, 'Forbidden action');
        $this->renderer = new PlainTextRenderer();
    }

    public function testOutput(): void
    {
        $output = ($this->renderer)($this->exception, false);

        static::assertEquals('403 Forbidden', $output);
    }

    public function testOutputWithTrace(): void
    {
        $output = ($this->renderer)($this->exception, true);

        static::assertStringContainsString('403 Forbidden', $output);
        static::assertStringContainsString('Trace', $output);
    }
}
