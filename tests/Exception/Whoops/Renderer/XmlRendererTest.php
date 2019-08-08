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

namespace Jgut\Slim\Exception\Tests\Whoops\Renderer;

use Jgut\Slim\Exception\Whoops\Renderer\XmlRenderer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpForbiddenException;
use Whoops\Exception\Inspector;

/**
 * Whoops custom XML HTTP exception renderer tests.
 */
class XmlRendererTest extends TestCase
{
    /**
     * @var XmlRenderer
     */
    protected $renderer;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->renderer = new XmlRenderer();
    }

    public function testXmlOutput()
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

        self::assertRegExp('!<id>.+</id>!', $output);
        self::assertRegExp('!<message>Forbidden</message>!', $output);
    }
}
