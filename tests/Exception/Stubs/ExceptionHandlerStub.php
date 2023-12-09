<?php

/*
 * (c) 2017-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-exception
 */

declare(strict_types=1);

namespace Jgut\Slim\Exception\Tests\Stubs;

use Jgut\Slim\Exception\ExceptionHandler;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\ErrorHandlerInterface;

/**
 * @internal
 */
class ExceptionHandlerStub extends ExceptionHandler
{
    /**
     * @param array{type: int, message: string, file: string, line: int}|null $lastError
     */
    public function __construct(
        ServerRequestInterface $request,
        ErrorHandlerInterface $errorHandler,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails,
        protected ?array $lastError = null,
    ) {
        parent::__construct($request, $errorHandler, $displayErrorDetails, $logErrors, $logErrorDetails);
    }

    protected function getLastError(): ?array
    {
        return $this->lastError ?? parent::getLastError();
    }
}
