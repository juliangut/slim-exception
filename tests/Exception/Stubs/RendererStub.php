<?php

/*
 * slim-exception (https://github.com/juliangut/slim-exception).
 * Slim exception handling.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-exception
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

declare(strict_types=1);

namespace Jgut\Slim\Exception\Tests\Stubs;

use Jgut\Slim\Exception\Whoops\Renderer\HtmlRenderer;
use Whoops\Handler\Handler;

/**
 * @internal
 */
class RendererStub extends HtmlRenderer
{
    public function getContentTypes(): array
    {
        return [];
    }

    public function handle(): ?int
    {
        echo $this->getException()
            ->getMessage();

        return Handler::QUIT;
    }
}
