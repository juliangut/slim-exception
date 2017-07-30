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

namespace Jgut\Slim\Exception\Tests;

use Jgut\Slim\Exception\HttpException;
use PHPUnit\Framework\TestCase;

/**
 * HTTP exception tests.
 */
class HttpExceptionTest extends TestCase
{
    public function testException()
    {
        $exception = new HttpException('message', 0, 400);

        self::assertNotNull($exception->getIdentifier());
        self::assertEquals(400, $exception->getHttpStatusCode());
    }
}
