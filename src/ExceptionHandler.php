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

use Jgut\HttpException\HttpException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * HTTP exception handler interface.
 */
interface ExceptionHandler
{
    /**
     * Handle HTTP exception.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param HttpException          $exception
     *
     * @return ResponseInterface
     */
    public function handleException(
        ServerRequestInterface $request,
        ResponseInterface $response,
        HttpException $exception
    ): ResponseInterface;
}
