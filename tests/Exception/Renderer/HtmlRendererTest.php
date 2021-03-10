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

use Jgut\Slim\Exception\Renderer\HtmlRenderer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpForbiddenException;

/**
 * HTML exception renderer tests.
 */
class HtmlRendererTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ServerRequestInterface
     */
    protected $request;

    /**
     * @var HtmlRenderer
     */
    protected $renderer;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $this->renderer = new HtmlRenderer();
    }

    public function testDefaultHttpExceptionOutput(): void
    {
        $exception = new HttpForbiddenException($this->request);
        $output = ($this->renderer)($exception, false);

        static::assertStringContainsString('<title>403 Forbidden</title>', $output);
        static::assertStringContainsString('<h1>403 Forbidden</h1>', $output);
        static::assertStringNotContainsString('<h2>Details</h2>', $output);
        static::assertStringContainsString('<p>Forbidden.</p>', $output);
    }

    public function testMessageHttpExceptionOutput(): void
    {
        $exception = new HttpForbiddenException($this->request, 'No access');
        $output = ($this->renderer)($exception, false);

        static::assertStringContainsString('<title>403 Forbidden</title>', $output);
        static::assertStringContainsString('<h1>403 Forbidden</h1>', $output);
        static::assertStringNotContainsString('<h2>Details</h2>', $output);
        static::assertStringContainsString('<p>No access</p>', $output);
    }

    public function testDescriptionHttpExceptionOutput(): void
    {
        $exception = new HttpForbiddenException($this->request, '');
        $output = ($this->renderer)($exception, false);

        static::assertStringContainsString('<title>403 Forbidden</title>', $output);
        static::assertStringContainsString('<h1>403 Forbidden</h1>', $output);
        static::assertStringNotContainsString('<h2>Details</h2>', $output);
        static::assertStringContainsString('<p>You are not permitted to perform the requested operation.</p>', $output);
    }

    public function testOutputWithTrace(): void
    {
        $exception = new \RuntimeException();
        $output = ($this->renderer)($exception, true);

        static::assertStringContainsString('<title>Slim Application error</title>', $output);
        static::assertStringContainsString('<h1>Slim Application error</h1>', $output);
        static::assertStringContainsString('<h2>Details</h2>', $output);
    }
}
