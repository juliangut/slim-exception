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

        self::assertContains('<title>403 Forbidden</title>', $output);
        self::assertContains('<h1>403 Forbidden</h1>', $output);
        self::assertNotContains('<h2>Details</h2>', $output);
        self::assertContains('<p>Forbidden.</p>', $output);
    }

    public function testMessageHttpExceptionOutput(): void
    {
        $exception = new HttpForbiddenException($this->request, 'No access');
        $output = ($this->renderer)($exception, false);

        self::assertContains('<title>403 Forbidden</title>', $output);
        self::assertContains('<h1>403 Forbidden</h1>', $output);
        self::assertNotContains('<h2>Details</h2>', $output);
        self::assertContains('<p>No access</p>', $output);
    }

    public function testDescriptionHttpExceptionOutput(): void
    {
        $exception = new HttpForbiddenException($this->request, '');
        $output = ($this->renderer)($exception, false);

        self::assertContains('<title>403 Forbidden</title>', $output);
        self::assertContains('<h1>403 Forbidden</h1>', $output);
        self::assertNotContains('<h2>Details</h2>', $output);
        self::assertContains('<p>You are not permitted to perform the requested operation.</p>', $output);
    }

    public function testOutputWithTrace(): void
    {
        $exception = new \RuntimeException();
        $output = ($this->renderer)($exception, true);

        self::assertContains('<title>Slim Application error</title>', $output);
        self::assertContains('<h1>Slim Application error</h1>', $output);
        self::assertContains('<h2>Details</h2>', $output);
    }
}
