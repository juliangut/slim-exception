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
use Psr\Http\Message\ServerRequestInterface;

/**
 * Default errors handler.
 */
class ExceptionHandler extends AbstractHttpExceptionHandler
{
    /**
     * {@inheritdoc}
     */
    protected function getExceptionOutput(
        string $contentType,
        HttpException $exception,
        ServerRequestInterface $request
    ): string {
        if (in_array($contentType, ['text/json', 'application/json', 'application/x-json'], true)) {
            return $this->getJsonError($exception);
        }

        if (in_array($contentType, ['text/xml', 'application/xml', 'application/x-xml'], true)) {
            return $this->getXmlError($exception);
        }

        if (in_array($contentType, ['text/html', 'application/xhtml+xml'], true)) {
            return $this->getHtmlError($exception);
        }

        // text/plain
        return $this->getTextError($exception);
    }

    /**
     * Get simple JSON formatted error.
     *
     * @param HttpException $exception
     *
     * @return string
     */
    protected function getJsonError(HttpException $exception): string
    {
        return sprintf(
            '{"error":{"ref":"%s","message":"Application error"}}',
            $exception->getIdentifier()
        );
    }

    /**
     * Get simple XML formatted error.
     *
     * @param HttpException $exception
     *
     * @return string
     */
    protected function getXmlError(HttpException $exception): string
    {
        return sprintf(
            '<?xml version="1.0" encoding="utf-8"?><root>' .
            '<error><ref>%s</ref><message>Application error</message></error>' .
            '</root>',
            $exception->getIdentifier()
        );
    }

    /**
     * Get simple HTML formatted error.
     *
     * @param HttpException $exception
     *
     * @return string
     */
    protected function getHtmlError(HttpException $exception): string
    {
        return sprintf(
            '<!DOCTYPE html><html lang="en"><head><meta http-equiv="Content-Type" content="text/html; ' .
            'charset=utf-8"><title>Application error</title><style>body{margin:0;padding:30px;font:12px/1.5 ' .
            'Helvetica,Arial,Verdana,sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;' .
            '}</style></head><body><h1>Application error (Ref. %s)</h1><p>An application error has occurred. ' .
            'Sorry for the temporary inconvenience.</p></body></html>',
            $exception->getIdentifier()
        );
    }

    /**
     * Get simple text formatted error.
     *
     * @param HttpException $exception
     *
     * @return string
     */
    protected function getTextError(HttpException $exception): string
    {
        return sprintf('(%s) Application error', $exception->getIdentifier());
    }
}
