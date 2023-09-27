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
use Whoops\Run as Whoops;

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
        $renderer->setException($exception);
        $renderer->setInspector($inspector);
        $renderer->setRun(new Whoops());

        ob_start();
        $renderer->handle();
        $output = ob_get_clean();

        $file = __FILE__;

        $expected /** @lang xml */
            = <<<EXPECTED
            <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
            <error>
              <type>Slim\\Exception\\HttpForbiddenException</type>
              <message><![CDATA[403 Forbidden]]></message>
              <code>403</code>
              <file>{$file}</file>
              <line>31</line>
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
        $renderer->setException($exception);
        $renderer->setInspector($inspector);
        $renderer->setPrettify(false);
        $renderer->setRun(new Whoops());

        ob_start();
        $renderer->handle();
        $output = ob_get_clean();

        $expected = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<error>'
            . '<type>Slim\Exception\HttpForbiddenException</type>'
            . '<message><![CDATA[403 Forbidden]]></message>'
            . '<code>403</code>'
            . '<file>' . __FILE__ . '</file>'
            . '<line>62</line>'
            . '<trace>';
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
        $renderer->setRun(new Whoops());

        ob_start();
        $renderer->handle();
        $output = ob_get_clean();

        $file = __FILE__;

        $expected /** @lang xml */
            = <<<EXPECTED
            <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
            <error>
              <type>Slim\\Exception\\HttpForbiddenException</type>
              <message><![CDATA[403 Forbidden]]></message>
              <code>403</code>
              <file>{$file}</file>
              <line>89</line>
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
        $renderer->setRun(new Whoops());

        ob_start();
        $renderer->handle();
        $output = ob_get_clean();

        $expected = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<error>'
            . '<type>Slim\Exception\HttpForbiddenException</type>'
            . '<message><![CDATA[403 Forbidden]]></message>'
            . '<code>403</code>'
            . '<file>' . __FILE__ . '</file>'
            . '<line>122</line>'
            . '</error>' . "\n";
        static::assertEquals($expected, $output);
    }
}
