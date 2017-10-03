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

namespace Jgut\Slim\Exception\Tests\Whoops\Handler;

use Jgut\Slim\Exception\Formatter\Text;
use Jgut\Slim\Exception\HttpExceptionFactory;
use Jgut\Slim\Exception\Whoops\Formatter\Html;
use Jgut\Slim\Exception\Whoops\Handler\ExceptionHandler;
use Negotiation\Negotiator;
use PHPUnit\Framework\TestCase;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;
use Whoops\Run as Whoops;

/**
 * Whoops HTTP exception handler tests.
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
        $this->handler = new ExceptionHandler(new Negotiator(), new Whoops());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /Formatter .+ for Whoops handler does not implement .+/
     */
    public function testInvalidHandler()
    {
        $formatter = $this->getMockBuilder(Text::class)
            ->getMock();
        /* @var Text $formatter */

        $this->handler->addFormatter($formatter);
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
        $request = Request::createFromEnvironment(Environment::mock(['HTTP_ACCEPT' => 'text/html']));

        $formatter = new Html();
        $formatter->handleUnconditionally(true);

        $this->handler->addFormatter($formatter, 'text/html');

        $response = $this->handler->handleException($request, new Response(), $exception);

        self::assertContains('Bad request', (string) $response->getBody());
    }
}
