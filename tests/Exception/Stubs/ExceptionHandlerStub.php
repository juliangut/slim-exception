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

use Jgut\Slim\Exception\ExceptionHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\ErrorHandlerInterface;

class ExceptionHandlerStub extends ExceptionHandler
{
    /**
     * @var array
     */
    protected $lastError;

    /**
     * AppStub constructor.
     *
     * @param ContainerInterface $container
     * @param array              $lastError
     */
    public function __construct(
        ServerRequestInterface $request,
        ErrorHandlerInterface $errorHandler,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails,
        array $lastError = []
    ) {
        parent::__construct($request, $errorHandler, $displayErrorDetails, $logErrors, $logErrorDetails);

        $this->lastError = $lastError;
    }

    /**
     * {@inheritdoc}
     */
    protected function getLastError(): array
    {
        if (\count($this->lastError)) {
            return $this->lastError;
        }

        return parent::getLastError();
    }
}
