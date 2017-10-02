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

use Jgut\Slim\Exception\Formatter\Whoops\FormatterTrait;
use Jgut\Slim\Exception\HttpExceptionFormatter;

class FormatterStub implements HttpExceptionFormatter
{
    use FormatterTrait;

    /**
     * {@inheritdoc}
     */
    public function getContentTypes(): array
    {
        return [];
    }
}
