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

use Jgut\Slim\Exception\Whoops\Renderer\HtmlRenderer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpInternalServerErrorException;
use Whoops\Exception\Inspector;
use Whoops\Run as Whoops;

/**
 * Whoops custom HTML exception renderer tests.
 */
class HtmlRendererTest extends TestCase
{
    /**
     * @var HtmlRenderer
     */
    protected $renderer;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->renderer = new HtmlRenderer();
    }

    public function testOutput()
    {
        /* @var ServerRequestInterface $request */
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $exception = new HttpInternalServerErrorException($request, 'Impossible error', new \ErrorException());
        $inspector = new Inspector($exception);
        $whoops = new Whoops();

        $this->renderer->handleUnconditionally(true);
        $this->renderer->setException($exception);
        $this->renderer->setInspector($inspector);
        $this->renderer->setRun($whoops);
        $this->renderer->setApplicationPaths([
            \dirname(__DIR__, 4) . '/src/Exception/HttpExceptionFactory.php',
            __FILE__,
        ]);

        \ob_start();
        $this->renderer->handle();
        $output = \ob_get_clean();

        self::assertContains(HttpInternalServerErrorException::class, $output);
        self::assertContains('<title>Application error</title>', $output);
        self::assertContains('<span class="exc-title-primary">HttpInternalServerErrorException</span>', $output);
        self::assertContains('<span>Impossible error</span>', $output);
    }
}
