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

use Jgut\Slim\Exception\Whoops\Formatter\Html;
use Whoops\Handler\Handler;

class FormatterStub extends Html
{
    /**
     * {@inheritdoc}
     */
    public function getContentTypes(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        echo $this->getException()->getMessage();

        return Handler::QUIT;
    }
}
