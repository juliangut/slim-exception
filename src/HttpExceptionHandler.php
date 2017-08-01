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

namespace Jgut\Slim\Exception;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * HTTP exception handler interface.
 */
interface HttpExceptionHandler
{
    /**
     * Handle HTTP exception.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param HttpException     $exception
     *
     * @return ResponseInterface
     */
    public function handleException(
        RequestInterface $request,
        ResponseInterface $response,
        HttpException $exception
    ): ResponseInterface;
}
