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
use Jgut\Slim\Exception\HttpExceptionHandler;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Stream;

/**
 * Abstract HTTP exception handler.
 */
abstract class AbstractHttpExceptionHandler implements HttpExceptionHandler
{
    /**
     * {@inheritdoc}
     */
    public function handleException(
        RequestInterface $request,
        ResponseInterface $response,
        HttpException $exception
    ): ResponseInterface {
        $contentType = $this->getContentType($request);

        $body = new Stream(fopen('php://temp', 'wb+'));
        $body->write($this->getExceptionOutput($contentType, $exception, $request));

        return $response
            ->withStatus($exception->getHttpStatusCode())
            ->withHeader('Content-Type', $contentType . '; charset=utf-8')
            ->withBody($body);
    }

    /**
     * Get request content type.
     *
     * @param RequestInterface $request
     *
     * @return string
     */
    protected function getContentType(RequestInterface $request): string
    {
        $knownTypes = $this->getContentTypes();
        $requestedTypes = array_map(
            function (string $contentType) {
                return explode(';', $contentType)[0];
            },
            explode(',', $request->getHeaderLine('Accept'))
        );
        $selectedTypes = array_intersect($requestedTypes, $knownTypes);

        if (count($selectedTypes)) {
            return reset($selectedTypes);
        }

        return $knownTypes[0];
    }

    /**
     * Get available content types.
     *
     * @return array
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
     * Get error content.
     *
     * @param string           $contentType
     * @param HttpException    $exception
     * @param RequestInterface $request
     *
     * @return string
     */
    abstract protected function getExceptionOutput(
        string $contentType,
        HttpException $exception,
        RequestInterface $request
    ): string;
}
