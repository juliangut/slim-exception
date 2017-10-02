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
 * XML HTTP exception formatter.
 */
class Xml implements HttpExceptionFormatter
{
    /**
     * {@inheritdoc}
     */
    public function getContentTypes(): array
    {
        return [
            'application/xml',
            'text/xml',
            'application/x-xml',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function formatException(HttpException $exception, ServerRequestInterface $request): string
    {
        return sprintf(
            '<?xml version="1.0" encoding="utf-8"?><root>' .
            '<error><id>%s</id><message>%s</message></error>' .
            '</root>',
            $exception->getIdentifier(),
            $exception->getMessage()
        );
    }
}
