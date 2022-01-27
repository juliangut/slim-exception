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

use Jgut\Slim\Exception\Whoops\Renderer\XmlRenderer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpForbiddenException;
use Whoops\Exception\Inspector;

/**
 * @internal
 */
class XmlRendererTest extends TestCase
{
    public function testOutput(): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $exception = new HttpForbiddenException($request, 'Forbidden action');
        $inspector = new Inspector($exception);

        $renderer = new XmlRenderer();
        $renderer->addTraceToOutput(true);
        $renderer->setException($exception);
        $renderer->setInspector($inspector);

        ob_start();
        $renderer->handle();
        $output = ob_get_clean();

        $expected = <<<'EXPECTED'
        <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
        <error>
          <message><![CDATA[403 Forbidden]]></message>
          <type>Slim\Exception\HttpForbiddenException</type>
          <trace>

        EXPECTED;
        static::assertStringContainsString($expected, $output);
    }

    public function testNotPrettifiedOutput(): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $exception = new HttpForbiddenException($request, 'Forbidden action');
        $inspector = new Inspector($exception);

        $renderer = new XmlRenderer();
        $renderer->addTraceToOutput(true);
        $renderer->setException($exception);
        $renderer->setInspector($inspector);
        $renderer->setPrettify(false);

        ob_start();
        $renderer->handle();
        $output = ob_get_clean();

        $expected = <<<'EXPECTED'
        <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
        <error><message><![CDATA[403 Forbidden]]></message><type>Slim\Exception\HttpForbiddenException</type><trace>
        EXPECTED;
        static::assertStringContainsString($expected, $output);
    }

    public function testNoTraceOutput(): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $exception = new HttpForbiddenException($request, 'Forbidden action');
        $inspector = new Inspector($exception);

        $renderer = new XmlRenderer();
        $renderer->addTraceToOutput(false);
        $renderer->setException($exception);
        $renderer->setInspector($inspector);

        ob_start();
        $renderer->handle();
        $output = ob_get_clean();

        $expected = <<<'EXPECTED'
        <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
        <error>
          <message><![CDATA[403 Forbidden]]></message>
        </error>

        EXPECTED;
        static::assertEquals($expected, $output);
    }

    public function testNotPrettifiedNoTraceOutput(): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $exception = new HttpForbiddenException($request, 'Forbidden action');
        $inspector = new Inspector($exception);

        $renderer = new XmlRenderer();
        $renderer->addTraceToOutput(false);
        $renderer->setException($exception);
        $renderer->setInspector($inspector);
        $renderer->setPrettify(false);

        ob_start();
        $renderer->handle();
        $output = ob_get_clean();

        $expected = <<<'EXPECTED'
        <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
        <error><message><![CDATA[403 Forbidden]]></message></error>

        EXPECTED;
        static::assertEquals($expected, $output);
    }
}
