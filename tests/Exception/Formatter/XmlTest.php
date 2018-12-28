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

namespace Jgut\Slim\Exception\Tests\Formatter;

use Jgut\HttpException\ForbiddenHttpException;
use Jgut\Slim\Exception\Formatter\Xml;
use PHPUnit\Framework\TestCase;
use Slim\Http\Request;

/**
 * XML HTTP exception formatter tests.
 */
class XmlTest extends TestCase
{
    /**
     * @var Xml
     */
    protected $formatter;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->formatter = new Xml();
    }

    public function testContentType()
    {
        $contentTypes = [
            'application/xml',
            'text/xml',
            'application/x-xml',
            'application/*+xml',
        ];

        self::assertEquals($contentTypes, $this->formatter->getContentTypes());
    }

    public function testOutput()
    {
        /* @var \Psr\Http\Message\ServerRequestInterface $request */
        $request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $exception = new ForbiddenHttpException('Forbidden "action"');

        $output = $this->formatter->formatException($exception, $request);

        self::assertRegExp('!<id>.+</id>!', $output);
        self::assertRegExp('!<message>Forbidden &quot;action&quot;</message>!', $output);
    }
}
