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
use Negotiation\Negotiator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Stream;

/**
 * Abstract HTTP exception handler.
 */
abstract class AbstractHttpExceptionHandler implements HttpExceptionHandler
{
    /**
     * Content type negotiator.
     *
     * @var Negotiator
     */
    protected $negotiator;

    /**
     * AbstractHttpExceptionHandler constructor.
     *
     * @param Negotiator $negotiator
     */
    public function __construct(Negotiator $negotiator)
    {
        $this->negotiator = $negotiator;
    }

    /**
     * {@inheritdoc}
     */
    public function handleException(
        ServerRequestInterface $request,
        ResponseInterface $response,
        HttpException $exception
    ): ResponseInterface {
        $contentType = $this->getContentType($request);

        return $response
            ->withStatus($exception->getStatusCode())
            ->withHeader('Content-Type', $contentType . '; charset=utf-8')
            ->withBody($this->getNewBody($this->getExceptionOutput($contentType, $exception, $request)));
    }

    /**
     * Get new body with content.
     *
     * @param string $content
     *
     * @return Stream
     */
    protected function getNewBody(string $content = ''): Stream
    {
        $body = new Stream(fopen('php://temp', 'wb+'));
        $body->write($content);

        return $body;
    }

    /**
     * Get request content type.
     *
     * @param ServerRequestInterface $request
     *
     * @return string
     */
    protected function getContentType(ServerRequestInterface $request): string
    {
        $contentType = trim($request->getHeaderLine('Accept'));
        $acceptedTypes = $this->getContentTypes();

        if ($contentType !== '') {
            try {
                /* @var \Negotiation\BaseAccept $best */
                $best = $this->negotiator->getBest($contentType, $acceptedTypes);

                if ($best) {
                    return $best->getValue();
                }
                // @codeCoverageIgnoreStart
            } catch (\Exception $exception) {
                // No action needed
            }
            // @codeCoverageIgnoreEnd
        }

        return $acceptedTypes[0];
    }

    /**
     * Get available content types.
     *
     * @return string[]
     */
    abstract protected function getContentTypes(): array;

    /**
     * Get error content.
     *
     * @param string                 $contentType
     * @param HttpException          $exception
     * @param ServerRequestInterface $request
     *
     * @return string
     */
    abstract protected function getExceptionOutput(
        string $contentType,
        HttpException $exception,
        ServerRequestInterface $request
    ): string;
}
