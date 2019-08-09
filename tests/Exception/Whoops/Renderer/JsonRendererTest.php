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

use Jgut\Slim\Exception\Whoops\Renderer\JsonRenderer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpForbiddenException;
use Whoops\Exception\Inspector;

/**
 * Whoops custom JSON exception renderer tests.
 */
class JsonRendererTest extends TestCase
{
    /**
     * @var JsonRenderer
     */
    protected $renderer;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->renderer = new JsonRenderer();
    }

    public function testOutput()
    {
        /* @var ServerRequestInterface $request */
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $exception = new HttpForbiddenException($request, 'Forbidden');
        $inspector = new Inspector($exception);

        $this->renderer->addTraceToOutput(true);
        $this->renderer->setException($exception);
        $this->renderer->setInspector($inspector);

        \ob_start();
        $this->renderer->handle();
        $output = \ob_get_clean();

        self::assertContains('"message": "Forbidden"', $output);
        self::assertContains('"trace": ', $output);
    }

    public function testNoTraceOutput()
    {
        /* @var ServerRequestInterface $request */
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $exception = new HttpForbiddenException($request, 'Forbidden');
        $inspector = new Inspector($exception);

        $this->renderer->addTraceToOutput(false);
        $this->renderer->setException($exception);
        $this->renderer->setInspector($inspector);

        \ob_start();
        $this->renderer->handle();
        $output = \ob_get_clean();

        self::assertContains('"message": "Forbidden"', $output);
        self::assertNotContains('"trace": ', $output);
    }
}
