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
     * List of JSON error messages.
     *
     * @var array<int, string>
     */
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

    /**
     * Json encode prettify.
     *
     * @var bool
     */
    protected $prettify = true;

    /**
     * @param bool $prettify
     */
    public function setPrettify(bool $prettify): void
    {
        $this->prettify = $prettify;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
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

        $json = \json_encode(['error' => $output], $this->getJsonFlags());
        if ($json === false) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException(self::JSON_ERROR_MESSAGES[\json_last_error()] ?? 'Unknown error.');
            // @codeCoverageIgnoreEnd
        }

        return $json;
    }

    /**
     * Get JSON encode flags.
     *
     * @return int
     */
    protected function getJsonFlags(): int
    {
        $jsonFlags = \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES | \JSON_PRESERVE_ZERO_FRACTION;
        if ($this->prettify) {
            $jsonFlags |= \JSON_PRETTY_PRINT;
        }

        return $jsonFlags;
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
