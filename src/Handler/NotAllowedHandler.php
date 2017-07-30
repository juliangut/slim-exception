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
 * Method not allowed error handler.
 */
class NotAllowedHandler extends AbstractHttpExceptionHandler
{
    /**
     * Allowed methods.
     *
     * @var array
     */
    protected $allowedMethods;

    /**
     * Invoke handler.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param array                  $methods
     *
     * @throws \InvalidArgumentException
     *
     * @return ResponseInterface
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $methods = []
    ): ResponseInterface {
        if ($request->getMethod() === 'OPTIONS') {
            $body = $this->getNewStream();
            $body->write(
                sprintf(
                    'Allowed method%s: %s',
                    count($methods) > 1 ? 's' : '',
                    implode(', ', $methods)
                )
            );

            return $response
                ->withStatus(200)
                ->withHeader('Content-Type', 'text/plain; charset=utf-8')
                ->withBody($body);
        }

        $this->allowedMethods = $methods;

        $exception = HttpExceptionFactory::methodNotAllowed(
            sprintf(
                'Method "%s" not allowed. Must be%s %s',
                $request->getMethod(),
                count($methods) > 1 ? ' one of' : '',
                implode(', ', $methods)
            )
        );

        return $this->handleError($request, $response, $exception);
    }

    /**
     * {@inheritdoc}
     */
    protected function getJsonError(HttpException $exception): string
    {
        return sprintf(
            '{"error":{"ref":"%s","message":"Method not allowed. Must be%s %s"}}',
            $exception->getIdentifier(),
            count($this->allowedMethods) > 1 ? ' one of' : '',
            implode(', ', $this->allowedMethods)
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getXmlError(HttpException $exception): string
    {
        return sprintf(
            '<?xml version="1.0" encoding="utf-8"?><root>' .
            '<error><ref>%s</ref><message>Method not allowed. Must be%s %s</message></error>' .
            '</root>',
            $exception->getIdentifier(),
            count($this->allowedMethods) > 1 ? ' one of' : '',
            implode(', ', $this->allowedMethods)
        );
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getHtmlError(HttpException $exception): string
    {
        return sprintf(
            '<!DOCTYPE html><html lang="en"><head><meta http-equiv="Content-Type" content="text/html; ' .
            'charset=utf-8"><title>Method not allowed</title><style>body{margin:0;padding:30px;font:12px/1.5 ' .
            'Helvetica,Arial,Verdana,sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;' .
            '}</style></head><body><h1>Method not allowed (Ref. %s)</h1><p>Must be%s %s.</p></body></html>',
            $exception->getIdentifier(),
            count($this->allowedMethods) > 1 ? ' one of' : '',
            implode(', ', $this->allowedMethods)
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getTextError(HttpException $exception): string
    {
        return sprintf(
            '(%s) Method not allowed. Must be%s %s',
            $exception->getIdentifier(),
            count($this->allowedMethods) > 1 ? ' one of' : '',
            implode(', ', $this->allowedMethods)
        );
    }
}
