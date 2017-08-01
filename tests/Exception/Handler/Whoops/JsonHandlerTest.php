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

use Jgut\Slim\Exception\Handler\Whoops\JsonHandler;
use Jgut\Slim\Exception\HttpExceptionFactory;
use PHPUnit\Framework\TestCase;
use Whoops\Exception\Inspector;

/**
 * Whoops custom JSON response handler tests.
 */
class JsonHandlerTest extends TestCase
{
    public function testJsonOutput()
    {
        $exception = HttpExceptionFactory::forbidden('Forbidden');
        $inspector = new Inspector($exception);

        $handler = new JsonHandler();
        $handler->addTraceToOutput(true);
        $handler->setException($exception);
        $handler->setInspector($inspector);

        ob_start();
        $handler->handle();
        $output = ob_get_clean();

        self::assertRegExp('/"id": ".+"/', $output);
        self::assertRegExp('/"message": "Forbidden"/', $output);
    }
}
