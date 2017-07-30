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

namespace Jgut\Slim\Exception\Tests\Stubs;

use Jgut\Slim\Exception\Handler\AbstractHttpExceptionHandler;
use Jgut\Slim\Exception\HttpException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HandlerStub extends AbstractHttpExceptionHandler
{
    /**
     * Mock invoking.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param HttpException          $exception
     *
     * @return ResponseInterface
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        HttpException $exception
    ): ResponseInterface {
        return $this->handleError($request, $response, $exception);
    }

    /**
     * {@inheritdoc}
     */
    protected function isCli()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getBasicJsonError(HttpException $exception): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getBasicXmlError(HttpException $exception): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getBasicHtmlError(HttpException $exception): string
    {
        return '';
    }
}
