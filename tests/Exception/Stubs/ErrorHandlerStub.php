<?php

/*
 * (c) 2017-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-exception
 */

declare(strict_types=1);

namespace Jgut\Slim\Exception\Tests\Stubs;

use Jgut\Slim\Exception\Handler\ErrorHandler;

/**
 * @internal
 */
class ErrorHandlerStub extends ErrorHandler
{
    protected function inCli(): bool
    {
        return false;
    }
}
