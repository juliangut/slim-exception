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

namespace Jgut\Slim\Exception\Tests\Handler;

use Jgut\Slim\Exception\Formatter\Html;
use Jgut\Slim\Exception\Formatter\Json;
use Jgut\Slim\Exception\Formatter\Text;
use Jgut\Slim\Exception\Handler\ExceptionHandler;
use Jgut\Slim\Exception\HttpExceptionFactory;
use Negotiation\Negotiator;
use PHPUnit\Framework\TestCase;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Default errors handler tests.
 */
class ExceptionHandlerTest extends TestCase
{
    /**
     * @var ExceptionHandler
     */
    protected $handler;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->handler = new ExceptionHandler(new Negotiator());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /No content type defined for .+ formatter/
     */
    public function testNoContentType()
    {
        $formatter = $this->getMockBuilder(Text::class)
            ->getMock();
        /* @var Text $formatter */

        $this->handler->addFormatter($formatter);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /No content type defined for .+ formatter/
     */
    public function testInvalidContentType()
    {
        $formatter = $this->getMockBuilder(Text::class)
            ->getMock();
        /* @var Text $formatter */

        $this->handler->addFormatter($formatter, 10);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage No formatters defined
     */
    public function testNoFormatters()
    {
        $exception = HttpExceptionFactory::badRequest();
        $request = Request::createFromEnvironment(Environment::mock());

        $this->handler->handleException($request, new Response(), $exception);
    }

    public function testHandle()
    {
        $exception = HttpExceptionFactory::badRequest();

        $textFormatter = $this->getMockBuilder(Text::class)
            ->getMock();
        /* @var Text $textFormatter */

        $htmlFormatter = $this->getMockBuilder(Html::class)
            ->getMock();
        $htmlFormatter->expects($this->once())
            ->method('formatException')
            ->with($exception)
            ->will($this->returnValue('<h1>Exception</h1>'));
        /* @var Html $htmlFormatter */

        $this->handler->addFormatter($textFormatter, 'text/plain');
        $this->handler->addFormatter($htmlFormatter, 'text/html');

        $request = Request::createFromEnvironment(Environment::mock(['HTTP_ACCEPT' => 'text/html']));

        $response = $this->handler->handleException($request, new Response(), $exception);

        self::assertEquals('text/html; charset=utf-8', $response->getHeaderLine('Content-Type'));
        self::assertEquals('<h1>Exception</h1>', (string) $response->getBody());
    }

    public function testDefaultHandle()
    {
        $exception = HttpExceptionFactory::badRequest();

        $formatter = $this->getMockBuilder(Json::class)
            ->getMock();
        $formatter->expects($this->once())
            ->method('formatException')
            ->with($exception)
            ->will($this->returnValue('Exception'));
        /* @var Json $formatter */

        $request = Request::createFromEnvironment(Environment::mock(['HTTP_ACCEPT' => 'text/html']));

        $this->handler->addFormatter($formatter, ['application/*+json', 'application/json']);

        $response = $this->handler->handleException($request, new Response(), $exception);

        self::assertEquals('application/json; charset=utf-8', $response->getHeaderLine('Content-Type'));
        self::assertEquals('Exception', (string) $response->getBody());
    }
}
