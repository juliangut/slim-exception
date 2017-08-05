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

namespace Jgut\Slim\Exception\Tests\Stubs;

use Jgut\Slim\Exception\Handler\AbstractHttpExceptionHandler;
use Jgut\Slim\Exception\HttpException;
use Psr\Http\Message\ServerRequestInterface;

class HandlerStub extends AbstractHttpExceptionHandler
{
    /**
     * {@inheritdoc}
     */
    protected function getContentTypes(): array
    {
        return [
            'text/plain',
            'text/json',
            'application/json',
            'application/x-json',
            'text/xml',
            'application/xml',
            'application/x-xml',
            'text/html',
            'application/xhtml+xml',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getExceptionOutput(
        string $contentType,
        HttpException $exception,
        ServerRequestInterface $request
    ): string {
        return $exception->getMessage();
    }
}
