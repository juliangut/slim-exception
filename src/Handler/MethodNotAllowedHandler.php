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

/**
 * Method not allowed error handler.
 */
class MethodNotAllowedHandler extends ExceptionHandler
{
    /**
     * Get simple text formatted error.
     *
     * @param HttpException $exception
     *
     * @return string
     */
    protected function getTextError(HttpException $exception): string
    {
        return sprintf('(%s) %s', $exception->getIdentifier(), $exception->getMessage());
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
            '{"error":{"ref":"%s","message":"%s"}}',
            $exception->getIdentifier(),
            $exception->getMessage()
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
            '<error><ref>%s</ref><message>%s</message></error>' .
            '</root>',
            $exception->getIdentifier(),
            $exception->getMessage()
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
            'charset=utf-8"><title>Method not allowed</title><style>body{margin:0;padding:30px;font:12px/1.5 ' .
            'Helvetica,Arial,Verdana,sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;' .
            '}</style></head><body><h1>Method not allowed (Ref. %s)</h1><p>%s</p></body></html>',
            $exception->getIdentifier(),
            $exception->getMessage()
        );
    }
}
