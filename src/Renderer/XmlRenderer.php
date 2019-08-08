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
 * XML HTTP exception renderer.
 */
class XmlRenderer implements ErrorRendererInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(\Throwable $exception, bool $displayErrorDetails): string
    {
        return \sprintf(
            '<?xml version="1.0" encoding="utf-8"?><root>' .
            '<error><id>%s</id><message>%s</message></error>' .
            '</root>',
            0, // TODO $exception->getIdentifier(),
            \htmlspecialchars($exception->getMessage(), \ENT_QUOTES)
        );
    }
}
