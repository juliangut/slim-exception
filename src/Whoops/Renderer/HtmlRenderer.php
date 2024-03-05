<?php

/*
 * (c) 2017-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-exception
 */

declare(strict_types=1);

namespace Jgut\Slim\Exception\Whoops\Renderer;

use Whoops\Handler\PrettyPageHandler;

/**
 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
 */
class HtmlRenderer extends PrettyPageHandler
{
    use RendererTrait;

    public function __construct(
        protected string $defaultTitle = 'Slim Application error',
    ) {
        parent::__construct();

        $this->setPageTitle($defaultTitle);
    }
}
