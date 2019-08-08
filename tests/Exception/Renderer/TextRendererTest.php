<?php

/*
 * slim-exception (https://github.com/juliangut/slim-exception).
 * Slim HTTP exceptions and exception handling.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-exception
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

declare(strict_types=1);

namespace Jgut\Slim\Exception\Tests\Renderer;

use Jgut\Slim\Exception\Renderer\TextRenderer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpForbiddenException;

/**
 * Plain text HTTP exception renderer tests.
 */
class TextRendererTest extends TestCase
{
    /**
     * @var TextRenderer
     */
    protected $renderer;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->renderer = new TextRenderer();
    }

    public function testOutput()
    {
        /* @var ServerRequestInterface $request */
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $exception = new HttpForbiddenException($request, 'Forbidden');

        $output = ($this->renderer)($exception, false);

        self::assertRegExp('/^\(.+\) Forbidden/', $output);
    }
}
