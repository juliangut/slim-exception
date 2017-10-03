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

namespace Jgut\Slim\Exception\Tests\Whoops\Formatter;

use Jgut\Slim\Exception\HttpExceptionFactory;
use Jgut\Slim\Exception\Tests\Stubs\FormatterStub;
use PHPUnit\Framework\TestCase;
use Slim\Http\Request;

/**
 * Whoops formatter trait tests.
 */
class FormatterTraitTest extends TestCase
{
    /**
     * @expectedException \LogicException
     * @expectedExceptionMessageRegExp /Calling .+::format is not possible/
     */
    public function testContentType()
    {
        /* @var \Psr\Http\Message\ServerRequestInterface $request */
        $request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();

        $formatter = new FormatterStub();

        $formatter->formatException(HttpExceptionFactory::unsupportedMediaType(), $request);
    }
}
