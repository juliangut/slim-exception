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

use JsonException;
use RuntimeException;
use Throwable;

class JsonRenderer extends AbstractRenderer
{
    protected const JSON_ERROR_MESSAGES = [
        \JSON_ERROR_DEPTH => 'Maximum stack depth exceeded.',
        \JSON_ERROR_STATE_MISMATCH => 'Underflow or the modes mismatch.',
        \JSON_ERROR_CTRL_CHAR => 'Unexpected control character found.',
        \JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON.',
        \JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded.',
        \JSON_ERROR_RECURSION => 'One or more recursive references in the value to be encoded.',
        \JSON_ERROR_INF_OR_NAN => 'One or more NAN or INF values in the value to be encoded.',
        \JSON_ERROR_UNSUPPORTED_TYPE => 'A value of a type that cannot be encoded was given.',
        \JSON_ERROR_INVALID_PROPERTY_NAME => 'A property name that cannot be encoded was given.',
        \JSON_ERROR_UTF16 => 'Malformed UTF-16 characters, possibly incorrectly encoded.',
    ];

    protected bool $prettify = true;

    public function setPrettify(bool $prettify): void
    {
        $this->prettify = $prettify;
    }

    /**
     * @throws RuntimeException
     */
    public function __invoke(Throwable $exception, bool $displayErrorDetails): string
    {
        $output = ['message' => $this->getErrorTitle($exception)];

        if ($displayErrorDetails) {
            $output['exception'] = [];

            do {
                $output['exception'][] = $this->formatException($exception);

                $exception = $exception->getPrevious();
            } while ($exception !== null);
        }

        try {
            $json = json_encode(['error' => $output], $this->getJsonFlags() | \JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException(
                self::JSON_ERROR_MESSAGES[$exception->getCode()] ?? 'Unknown error.',
                0,
                $exception,
            );
            // @codeCoverageIgnoreEnd
        }

        return $json;
    }

    protected function getJsonFlags(): int
    {
        $jsonFlags = \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES | \JSON_PRESERVE_ZERO_FRACTION;
        if ($this->prettify) {
            $jsonFlags |= \JSON_PRETTY_PRINT;
        }

        return $jsonFlags;
    }

    /**
     * @return array<string, mixed>
     */
    private function formatException(Throwable $exception): array
    {
        return [
            'type' => $exception::class,
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ];
    }
}
