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

use Jgut\Slim\Exception\Whoops\Renderer\PlainTextRenderer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotImplementedException;
use Whoops\Exception\Inspector;

/**
 * Whoops custom plain text exception renderer tests.
 */
class PlainTextRendererTest extends TestCase
{
    /**
     * @var PlainTextRenderer
     */
    protected $renderer;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->renderer = new PlainTextRenderer();
    }

    public function testOutput()
    {
        /* @var ServerRequestInterface $request */
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $originalException = new \ErrorException('Original exception');
        $exception = new HttpNotImplementedException($request, null, $originalException);
        $inspector = new Inspector($exception);

        $handler = new PlainTextRenderer();
        $handler->addTraceFunctionArgsToOutput(true);
        $handler->setException($exception);
        $handler->setInspector($inspector);

        \ob_start();
        $handler->handle();
        $output = \ob_get_clean();

        self::assertContains('Slim\\Exception\\HttpNotImplementedException: Not implemented', $output);
        self::assertContains('Stack trace:', $output);
    }

    public function testNoTraceOutput()
    {
        /* @var ServerRequestInterface $request */
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $exception = new HttpNotImplementedException($request);
        $inspector = new Inspector($exception);

        $this->renderer->addTraceToOutput(false);
        $this->renderer->setException($exception);
        $this->renderer->setInspector($inspector);

        \ob_start();
        $this->renderer->handle();
        $output = \ob_get_clean();

        self::assertContains('Not implemented', $output);
        self::assertNotContains('Stack trace:', $output);
    }
}
