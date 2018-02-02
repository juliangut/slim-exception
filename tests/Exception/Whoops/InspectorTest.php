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
use Jgut\Slim\Exception\Tests\Stubs\InspectorStub;
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
        $exception = HttpExceptionFactory::internalServerError('', '', null, $originalException);

        $inspector = new Inspector($exception);

        self::assertEquals($exception, $inspector->getException());
    }

    public function testTraceFrames()
    {
        $originalException = new \InvalidArgumentException();
        $exception = HttpExceptionFactory::internalServerError('', '', null, $originalException);

        $inspector = new InspectorStub($exception);

        $frames = $inspector->getTraceFrames();

        self::assertEquals(HttpExceptionFactory::class, $frames[0]->getClass());
        self::assertEquals('internalServerError', $frames[0]->getFunction());
        self::assertEquals(__CLASS__, $frames[count($frames) - 1]->getClass());
        self::assertEquals(__FUNCTION__, $frames[count($frames) - 1]->getFunction());
    }
}
