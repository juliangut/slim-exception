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

namespace Jgut\Slim\Exception\Renderer;

use Slim\Exception\HttpException;
use Slim\Interfaces\ErrorRendererInterface;

/**
 * Abstract exception renderer.
 */
abstract class AbstractRenderer implements ErrorRendererInterface
{
    /**
     * @var string
     */
    protected $defaultTitle;

    /**
     * @var string
     */
    protected $defaultDescription;

    /**
     * Exception renderer constructor.
     *
     * @param string $defaultTitle
     * @param string $defaultDescription
     */
    public function __construct(
        string $defaultTitle = 'Slim Application error',
        string $defaultDescription = 'A website error has occurred. Sorry for the temporary inconvenience.'
    ) {
        $this->defaultTitle = $defaultTitle;
        $this->defaultDescription = $defaultDescription;
    }

    /**
     * Get exception title.
     *
     * @param \Throwable $exception
     *
     * @return string
     */
    protected function getErrorTitle(\Throwable $exception): string
    {
        return $exception instanceof HttpException ? $exception->getTitle() : $this->defaultTitle;
    }

    /**
     * Get exception description.
     *
     * @param \Throwable $exception
     *
     * @return string
     */
    protected function getErrorDescription(\Throwable $exception): string
    {
        if (!$exception instanceof HttpException) {
            return $this->defaultDescription;
        }

        return $exception->getMessage() !== '' ? $exception->getMessage() : $exception->getDescription();
    }
}
