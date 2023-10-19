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
 * @internal
 */
class XmlRendererTest extends TestCase
{
    public function testOutput(): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $exception = new HttpForbiddenException($request, 'Forbidden action');
        $output = (new XmlRenderer())($exception, false);

        $expected /** @lang xml */
            = <<<'EXPECTED'
            <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
            <error>
              <message><![CDATA[403 Forbidden]]></message>
            </error>
            EXPECTED;
        static::assertEquals($expected, $output);
    }

    public function testNotPrettifiedOutput(): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $exception = new HttpForbiddenException($request, 'Forbidden action');
        $renderer = new XmlRenderer();
        $renderer->setPrettify(false);

        $output = $renderer($exception, false);

        $expected /** @lang xml */
            = <<<'EXPECTED'
            <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
            <error><message><![CDATA[403 Forbidden]]></message></error>
            EXPECTED;
        static::assertEquals($expected, $output);
    }

    public function testOutputWithTrace(): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $exception = new HttpForbiddenException($request, 'Forbidden action');
        $output = (new XmlRenderer())($exception, true);

        $expected /** @lang xml */
            = <<<'EXPECTED'
            <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
            <error>
              <message><![CDATA[403 Forbidden]]></message>
              <exception>
            EXPECTED;
        static::assertStringContainsString($expected, $output);
    }

    public function testNotPrettifiedOutputWithTrace(): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $exception = new HttpForbiddenException($request, 'Forbidden action');
        $renderer = new XmlRenderer();
        $renderer->setPrettify(false);

        $output = $renderer($exception, true);

        $expected /** @lang xml */
            = <<<'EXPECTED'
            <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
            <error><message><![CDATA[403 Forbidden]]></message><exception>
            EXPECTED;
        static::assertStringContainsString($expected, $output);
    }
}
