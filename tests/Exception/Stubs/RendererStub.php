<?php

/*
 * (c) 2017-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-exception
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
