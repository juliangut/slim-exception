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
        /* @var ServerRequestInterface $request */
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $this->exception = new HttpForbiddenException($request);
        $this->renderer = new HtmlRenderer();
    }

    public function testOutput()
    {
        $output = ($this->renderer)($this->exception, false);

        self::assertContains('<title>Application error</title>', $output);
        self::assertContains('<h1>Application error</h1>', $output);
        self::assertContains(
            '<p>An application error has occurred. Sorry for the temporary inconvenience</p>',
            $output
        );
        self::assertNotContains('<h3>Trace</h3>', $output);
    }

    public function testOutputWithTrace()
    {
        $output = ($this->renderer)($this->exception, true);

        self::assertContains('<title>Application error</title>', $output);
        self::assertContains('<h1>Application error</h1>', $output);
        self::assertNotContains(
            '<p>An application error has occurred. Sorry for the temporary inconvenience</p>',
            $output
        );
        self::assertContains('<h3>Trace</h3>', $output);
    }
}
