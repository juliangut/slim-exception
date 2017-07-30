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

namespace Jgut\Slim\Exception\Handler;

use Jgut\Slim\Exception\HttpException;
use Jgut\Slim\Exception\HttpExceptionFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Default errors handler.
 */
class ErrorHandler extends AbstractHttpExceptionHandler
{
    /**
     * Invoke handler.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param \Throwable             $exception
     *
     * @return ResponseInterface
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        \Throwable $exception
    ): ResponseInterface {
        if (!$exception instanceof HttpException) {
            $exception = HttpExceptionFactory::internalServerError(null, null, $exception);
        }

        return $this->handleError($request, $response, $exception);
    }
}
