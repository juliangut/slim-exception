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
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $this->exception = new HttpForbiddenException($request, 'Forbidden action');
    }

    public function testOutput(): void
    {
        $output = (new XmlRenderer())($this->exception, false);

        $expected = <<<'EXPECTED'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<error>
  <message><![CDATA[403 Forbidden]]></message>
</error>
EXPECTED;
        static::assertEquals($expected, $output);
    }

    public function testNotPrettifiedOutput(): void
    {
        $renderer = new XmlRenderer();
        $renderer->setPrettify(false);

        $output = $renderer($this->exception, false);

        $expected = <<<'EXPECTED'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<error><message><![CDATA[403 Forbidden]]></message></error>
EXPECTED;
        static::assertEquals($expected, $output);
    }

    public function testOutputWithTrace(): void
    {
        $output = (new XmlRenderer())($this->exception, true);

        $expected = <<<'EXPECTED'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<error>
  <message><![CDATA[403 Forbidden]]></message>
  <exception>

EXPECTED;
        static::assertStringContainsString($expected, $output);
    }

    public function testNotPrettifiedOutputWithTrace(): void
    {
        $renderer = new XmlRenderer();
        $renderer->setPrettify(false);

        $output = $renderer($this->exception, true);

        $expected = <<<'EXPECTED'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<error><message><![CDATA[403 Forbidden]]></message><exception>
EXPECTED;
        static::assertStringContainsString($expected, $output);
    }
}
