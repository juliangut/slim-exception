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

use Jgut\Slim\Exception\Whoops\Handler\ErrorHandler;

/**
 * Custom Whoops error handler stub.
 */
class WhoopsErrorHandlerStub extends ErrorHandler
{
    /**
     * {@inheritdoc}
     */
    protected function inCli(): bool
    {
        return false;
    }
}
