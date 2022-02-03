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

use ErrorException;
use Jgut\Slim\Exception\Whoops\Renderer\HtmlRenderer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpInternalServerErrorException;
use Whoops\Exception\Inspector;
use Whoops\Run as Whoops;

/**
 * @internal
 */
class HtmlRendererTest extends TestCase
{
    protected HtmlRenderer $renderer;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->renderer = new HtmlRenderer();
    }

    public function testOutput(): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $exception = new HttpInternalServerErrorException($request, 'Impossible error', new ErrorException());
        $inspector = new Inspector($exception);
        $whoops = new Whoops();

        $this->renderer->handleUnconditionally(true);
        $this->renderer->setException($exception);
        $this->renderer->setInspector($inspector);
        $this->renderer->setRun($whoops);
        $this->renderer->setApplicationPaths([
            __DIR__ . '/../../../../src/Exception/HttpExceptionFactory.php',
            __FILE__,
        ]);

        ob_start();
        $this->renderer->handle();
        $output = ob_get_clean();

        static::assertStringContainsString(HttpInternalServerErrorException::class, $output);
        static::assertStringContainsString('<title>Slim Application error</title>', $output);
        static::assertStringContainsString(
            '<span class="exc-title-primary">HttpInternalServerErrorException</span>',
            $output,
        );
        static::assertStringContainsString('<span>Impossible error</span>', $output);
    }
}
