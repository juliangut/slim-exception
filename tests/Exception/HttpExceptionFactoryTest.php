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

use Jgut\Slim\Exception\HttpExceptionFactory;
use PHPUnit\Framework\TestCase;

/**
 * HTTP exception factory tests.
 */
class HttpExceptionFactoryTest extends TestCase
{
    public function testCustomException()
    {
        $previous = new \Exception();
        $exception = HttpExceptionFactory::create('Message', 10, 400, $previous);

        self::assertEquals('Message', $exception->getMessage());
        self::assertEquals(10, $exception->getCode());
        self::assertEquals(400, $exception->getHttpStatusCode());
        self::assertEquals($previous, $exception->getPrevious());
    }

    public function testBadRequest()
    {
        $exception = HttpExceptionFactory::badRequest('');

        self::assertEquals('Bad request', $exception->getMessage());
        self::assertEquals(400, $exception->getCode());
        self::assertEquals(400, $exception->getHttpStatusCode());
    }

    public function testUnauthorized()
    {
        $exception = HttpExceptionFactory::unauthorized();

        self::assertEquals('Unauthorized', $exception->getMessage());
        self::assertEquals(401, $exception->getCode());
        self::assertEquals(401, $exception->getHttpStatusCode());
    }

    public function testForbidden()
    {
        $exception = HttpExceptionFactory::forbidden();

        self::assertEquals('Forbidden', $exception->getMessage());
        self::assertEquals(403, $exception->getCode());
        self::assertEquals(403, $exception->getHttpStatusCode());
    }

    public function testNotFound()
    {
        $exception = HttpExceptionFactory::notFound();

        self::assertEquals('Not found', $exception->getMessage());
        self::assertEquals(404, $exception->getCode());
        self::assertEquals(404, $exception->getHttpStatusCode());
    }

    public function testMethodNotAllowed()
    {
        $exception = HttpExceptionFactory::methodNotAllowed();

        self::assertEquals('Method not allowed', $exception->getMessage());
        self::assertEquals(405, $exception->getCode());
        self::assertEquals(405, $exception->getHttpStatusCode());
    }

    public function testNotAcceptable()
    {
        $exception = HttpExceptionFactory::notAcceptable();

        self::assertEquals('Not acceptable', $exception->getMessage());
        self::assertEquals(406, $exception->getCode());
        self::assertEquals(406, $exception->getHttpStatusCode());
    }

    public function testConflict()
    {
        $exception = HttpExceptionFactory::conflict();

        self::assertEquals('Conflict', $exception->getMessage());
        self::assertEquals(409, $exception->getCode());
        self::assertEquals(409, $exception->getHttpStatusCode());
    }

    public function testGone()
    {
        $exception = HttpExceptionFactory::gone();

        self::assertEquals('Gone', $exception->getMessage());
        self::assertEquals(410, $exception->getCode());
        self::assertEquals(410, $exception->getHttpStatusCode());
    }

    public function testUnsupportedMediaType()
    {
        $exception = HttpExceptionFactory::unsupportedMediaType();

        self::assertEquals('Unsupported media type', $exception->getMessage());
        self::assertEquals(415, $exception->getCode());
        self::assertEquals(415, $exception->getHttpStatusCode());
    }

    public function testUnprocessableEntity()
    {
        $exception = HttpExceptionFactory::unprocessableEntity();

        self::assertEquals('Unprocessable entity', $exception->getMessage());
        self::assertEquals(422, $exception->getCode());
        self::assertEquals(422, $exception->getHttpStatusCode());
    }

    public function testTooManyRequests()
    {
        $exception = HttpExceptionFactory::tooManyRequests();

        self::assertEquals('Too many requests', $exception->getMessage());
        self::assertEquals(429, $exception->getCode());
        self::assertEquals(429, $exception->getHttpStatusCode());
    }

    public function testInternalServerError()
    {
        $exception = HttpExceptionFactory::internalServerError();

        self::assertEquals('Internal server error', $exception->getMessage());
        self::assertEquals(500, $exception->getCode());
        self::assertEquals(500, $exception->getHttpStatusCode());
    }

    public function testNotImplemented()
    {
        $exception = HttpExceptionFactory::notImplemented();

        self::assertEquals('Not implemented', $exception->getMessage());
        self::assertEquals(501, $exception->getCode());
        self::assertEquals(501, $exception->getHttpStatusCode());
    }
}
