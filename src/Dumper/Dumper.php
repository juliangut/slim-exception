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

namespace Jgut\Slim\Exception\Dumper;

use Jgut\Slim\Exception\HttpException;
use Psr\Http\Message\RequestInterface;

/**
 * Exception dumper interface.
 */
interface Dumper
{
    /**
     * Get formatted exception output.
     *
     * @param string           $contentType
     * @param HttpException    $exception
     * @param RequestInterface $request
     *
     * @return string
     */
    public function getFormattedException(
        string $contentType,
        HttpException $exception,
        RequestInterface $request
    ): string;
}
