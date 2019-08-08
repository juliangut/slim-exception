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

use Jgut\Slim\Exception\Renderer\XmlRenderer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpForbiddenException;

/**
 * XML HTTP exception renderer tests.
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

    public function testOutput()
    {
        /* @var ServerRequestInterface $request */
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $exception = new HttpForbiddenException($request, 'Forbidden "action"');

        $output = ($this->renderer)($exception, false);

        self::assertRegExp('!<id>.+</id>!', $output);
        self::assertRegExp('!<message>Forbidden &quot;action&quot;</message>!', $output);
    }
}
