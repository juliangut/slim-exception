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

namespace Jgut\Slim\Exception\Renderer;

use Slim\Interfaces\ErrorRendererInterface;

/**
 * JSON HTTP exception renderer.
 */
class JsonRenderer implements ErrorRendererInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(\Throwable $exception, bool $displayErrorDetails): string
    {
        return (string) \json_encode(
            [
                'error' => [
                    'id' => 0, // TODO $exception->getIdentifier(),
                    'message' => $exception->getMessage(),
                ],
            ],
            \JSON_UNESCAPED_UNICODE
        );
    }
}
