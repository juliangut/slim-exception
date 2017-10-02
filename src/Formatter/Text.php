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

namespace Jgut\Slim\Exception\Formatter;

use Jgut\Slim\Exception\HttpException;
use Jgut\Slim\Exception\HttpExceptionFormatter;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Plain text HTTP exception formatter.
 */
class Text implements HttpExceptionFormatter
{
    /**
     * {@inheritdoc}
     */
    public function getContentTypes(): array
    {
        return [
            'text/plain',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function formatException(HttpException $exception, ServerRequestInterface $request): string
    {
        return sprintf('(%s) %s', $exception->getIdentifier(), $exception->getMessage());
    }
}
