<?php

/*
 * (c) 2017-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-exception
 */

declare(strict_types=1);

namespace Jgut\Slim\Exception\Tests\Stubs;

use Jgut\Slim\Exception\Whoops\Handler\ErrorHandler;

/**
 * @internal
 */
class WhoopsErrorHandlerStub extends ErrorHandler
{
    protected function inCli(): bool
    {
        return false;
    }
}
