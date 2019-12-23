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

/**
 * JSON exception renderer.
 */
class JsonRenderer extends AbstractRenderer
{
    /**
     * JSON encoding options.
     * Preserve float values and encode &, ', ", < and > characters in the resulting JSON.
     */
    const JSON_ENCODE_OPTIONS = \JSON_UNESCAPED_UNICODE
        | \JSON_UNESCAPED_SLASHES
        | \JSON_PRESERVE_ZERO_FRACTION
        | \JSON_HEX_AMP
        | \JSON_HEX_APOS
        | \JSON_HEX_QUOT
        | \JSON_HEX_TAG
        | \JSON_PRETTY_PRINT;

    /**
     * {@inheritdoc}
     */
    public function __invoke(\Throwable $exception, bool $displayErrorDetails): string
    {
        $output = ['message' => $this->getErrorTitle($exception)];

        if ($displayErrorDetails) {
            $output['exception'] = [];

            do {
                $output['exception'][] = $this->formatException($exception);
            } while ($exception = $exception->getPrevious());
        }

        $jsonEncodeOptions = static::JSON_ENCODE_OPTIONS
            | (\PHP_VERSION_ID >= 70400 ? \JSON_THROW_ON_ERROR : \JSON_PARTIAL_OUTPUT_ON_ERROR);

        return (string) \json_encode(['error' => $output], $jsonEncodeOptions);
    }

    /**
     * @param \Throwable $exception
     *
     * @return mixed[]
     */
    private function formatException(\Throwable $exception): array
    {
        return [
            'type' => \get_class($exception),
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ];
    }
}
