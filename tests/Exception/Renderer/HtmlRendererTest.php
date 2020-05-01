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
     * @var HttpForbiddenException
     */
    protected $exception;

    /**
     * @var HtmlRenderer
     */
    protected $renderer;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $this->exception = new HttpForbiddenException($request);
        $this->renderer = new HtmlRenderer();
    }

    public function testOutput(): void
    {
        $output = ($this->renderer)($this->exception, false);

        self::assertContains('<title>403 Forbidden</title>', $output);
        self::assertContains('<h1>403 Forbidden</h1>', $output);
        self::assertNotContains('<h2>Details</h2>', $output);
    }

    public function testOutputWithTrace(): void
    {
        $output = ($this->renderer)($this->exception, true);

        self::assertContains('<title>403 Forbidden</title>', $output);
        self::assertContains('<h1>403 Forbidden</h1>', $output);
        self::assertContains('<h2>Details</h2>', $output);
    }
}
