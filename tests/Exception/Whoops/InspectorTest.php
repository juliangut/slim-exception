<?php

/*
 * slim-exception (https://github.com/juliangut/slim-exception).
 * Slim exception handling.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-exception
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

declare(strict_types=1);

namespace Jgut\Slim\Exception\Tests\Whoops;

use Jgut\Slim\Exception\Tests\Stubs\InspectorStub;
use Jgut\Slim\Exception\Whoops\Inspector;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpInternalServerErrorException;

/**
 * Custom Whoops inspector tests.
 */
class InspectorTest extends TestCase
{
    public function testAssign(): void
    {
        /* @var ServerRequestInterface $request */
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $originalException = new \InvalidArgumentException();
        $exception = new HttpInternalServerErrorException($request, null, $originalException);

        $inspector = new Inspector($exception);

        self::assertEquals($exception, $inspector->getException());
    }

    public function testStackTraceFrames(): void
    {
        /* @var ServerRequestInterface $request */
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $originalException = new \InvalidArgumentException();
        $exception = new HttpInternalServerErrorException($request, null, $originalException);

        $inspector = new InspectorStub($exception);

        $frames = $inspector->getTraceFrames();

        self::assertEquals(__CLASS__, $frames[\count($frames) - 1]->getClass());
        self::assertEquals(__FUNCTION__, $frames[\count($frames) - 1]->getFunction());
    }
}
