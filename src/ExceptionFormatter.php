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
use Psr\Http\Message\ServerRequestInterface;

/**
 * HTTP exception formatter interface.
 */
interface ExceptionFormatter
{
    /**
     * Get supported content types.
     *
     * @return string[]
     */
    public function getContentTypes(): array;

    /**
     * Format HTTP exception.
     *
     * @param HttpException          $exception
     * @param ServerRequestInterface $request
     *
     * @return string
     */
    public function formatException(HttpException $exception, ServerRequestInterface $request): string;
}
