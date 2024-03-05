<?php

/*
 * (c) 2017-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-exception
 */

declare(strict_types=1);

namespace Jgut\Slim\Exception\Renderer;

use Slim\Exception\HttpException;
use Slim\Interfaces\ErrorRendererInterface;
use Throwable;

abstract class AbstractRenderer implements ErrorRendererInterface
{
    public function __construct(
        protected string $defaultTitle = 'Slim Application error',
        protected string $defaultDescription = 'An error has occurred. Sorry for the temporary inconvenience.',
    ) {}

    protected function getErrorTitle(Throwable $exception): string
    {
        return $exception instanceof HttpException ? $exception->getTitle() : $this->defaultTitle;
    }

    protected function getErrorDescription(Throwable $exception): string
    {
        if (!$exception instanceof HttpException) {
            return $this->defaultDescription;
        }

        return $exception->getMessage() !== '' ? $exception->getMessage() : $exception->getDescription();
    }
}
