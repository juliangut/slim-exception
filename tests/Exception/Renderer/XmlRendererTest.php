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

use Jgut\Slim\Exception\Renderer\XmlRenderer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpForbiddenException;

/**
 * XML exception renderer tests.
 */
class XmlRendererTest extends TestCase
{
    /**
     * @var HttpForbiddenException
     */
    protected $exception;

    /**
     * @var XmlRenderer
     */
    protected $renderer;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        /* @var ServerRequestInterface $request */
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $this->exception = new HttpForbiddenException($request, 'Forbidden action');
        $this->renderer = new XmlRenderer();
    }

    public function testOutput()
    {
        $output = ($this->renderer)($this->exception, false);

        self::assertContains('<message><![CDATA[Forbidden action]]></message>', $output);
        self::assertNotContains('<trace>', $output);
    }

    public function testOutputWithTrace()
    {
        $output = ($this->renderer)($this->exception, true);

        self::assertContains('<message><![CDATA[Forbidden action]]></message>', $output);
        self::assertContains('<trace>', $output);
    }
}
