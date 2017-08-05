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

namespace Jgut\Slim\Exception\Tests\Dumper\Whoops;

use Jgut\Slim\Exception\Handler\Whoops\TextHandler;
use Jgut\Slim\Exception\HttpExceptionFactory;
use PHPUnit\Framework\TestCase;
use Whoops\Exception\Inspector;

/**
 * Whoops custom plain text response handler tests.
 */
class TextHandlerTest extends TestCase
{
    public function testNoTraceOutput()
    {
        $exception = HttpExceptionFactory::tooManyRequests();
        $inspector = new Inspector($exception);

        $handler = new TextHandler();
        $handler->addTraceToOutput(false);
        $handler->setException($exception);
        $handler->setInspector($inspector);

        ob_start();
        $handler->handle();
        $output = ob_get_clean();

        self::assertNotRegExp('/Stack trace:/', $output);
    }

    public function testTextOutput()
    {
        $originalException = new \ErrorException('Original exception');
        $exception = HttpExceptionFactory::tooManyRequests(null, null, null, $originalException);
        $inspector = new Inspector($exception);

        $handler = new TextHandler();
        $handler->addTraceFunctionArgsToOutput(true);
        $handler->setException($exception);
        $handler->setInspector($inspector);

        ob_start();
        $handler->handle();
        $output = ob_get_clean();

        self::assertRegExp('/^\(.+\) Jgut\\\\Slim\\\\Exception\\\\HttpException/', $output);
        self::assertRegExp('/Stack trace:/', $output);
    }
}
