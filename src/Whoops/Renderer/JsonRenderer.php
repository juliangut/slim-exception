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

namespace Jgut\Slim\Exception\Whoops\Renderer;

use Jgut\Slim\Exception\Whoops\Inspector;
use Whoops\Handler\Handler;
use Whoops\Handler\JsonResponseHandler;

/**
 * Whoops custom JSON exception renderer.
 */
class JsonRenderer extends JsonResponseHandler
{
    use RendererTrait;

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
     * List of JSON error messages.
     *
     * @var array<int, string>
     */
    protected const JSON_ERROR_MESSAGES = [
        \JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
        \JSON_ERROR_STATE_MISMATCH => 'Underflow or the modes mismatch',
        \JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
        \JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
        \JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded',
        \JSON_ERROR_RECURSION => 'One or more recursive references in the value to be encoded',
        \JSON_ERROR_INF_OR_NAN => 'One or more NAN or INF values in the value to be encoded',
        \JSON_ERROR_UNSUPPORTED_TYPE => 'A value of a type that cannot be encoded was given',
        \JSON_ERROR_INVALID_PROPERTY_NAME => 'A property name that cannot be encoded was given',
        \JSON_ERROR_UTF16 => 'Malformed UTF-16 characters, possibly incorrectly encoded',
    ];

    /**
     * JsonHandler constructor.
     *
     * @param string $defaultTitle
     */
    public function __construct(string $defaultTitle = 'Slim Application error')
    {
        $this->defaultTitle = $defaultTitle;

        $this->addTraceToOutput(true);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function handle(): int
    {
        $exception = $this->getException();

        $inspector = new Inspector($exception);
        $this->setInspector($inspector);

        /** @var bool $addTrace */
        $addTrace = $this->addTraceToOutput();

        $error = $this->getExceptionData($inspector, $addTrace);

        $json = \json_encode($error, static::JSON_ENCODE_OPTIONS);
        if ($json === false) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException(self::JSON_ERROR_MESSAGES[\json_last_error()] ?? 'Unknown error');
            // @codeCoverageIgnoreEnd
        }

        echo $json;

        return Handler::QUIT;
    }
}
