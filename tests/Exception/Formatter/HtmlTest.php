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

use Jgut\Slim\Exception\Formatter\Html;
use Jgut\Slim\Exception\HttpExceptionFactory;
use PHPUnit\Framework\TestCase;
use Slim\Http\Request;

/**
 * HTML HTTP exception formatter tests.
 */
class HtmlTest extends TestCase
{
    /**
     * @var Html
     */
    protected $formatter;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->formatter = new Html();
    }

    public function testContentType()
    {
        $contentTypes = [
            'text/html',
            'application/xhtml+xml',
        ];

        self::assertEquals($contentTypes, $this->formatter->getContentTypes());
    }

    public function testOutput()
    {
        /* @var \Psr\Http\Message\ServerRequestInterface $request */
        $request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $exception = HttpExceptionFactory::forbidden('Forbidden');

        $output = $this->formatter->formatException($exception, $request);

        self::assertRegExp('!<title>Application error</title>!', $output);
        self::assertRegExp('!<h1>Application error <span>(.+)</span></h1>!', $output);
    }
}
