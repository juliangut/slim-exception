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

namespace Jgut\Slim\Exception\Tests\Whoops;

use Jgut\Slim\Exception\HttpExceptionFactory;
use Jgut\Slim\Exception\Whoops\Inspector;
use PHPUnit\Framework\TestCase;

/**
 * Custom Whoops inspector tests.
 */
class InspectorTest extends TestCase
{
    public function testAssign()
    {
        $originalException = new \InvalidArgumentException();
        $exception = HttpExceptionFactory::internalServerError(null, null, null, $originalException);

        $inspector = new Inspector($exception);

        self::assertEquals($originalException, $inspector->getException());
    }
}
