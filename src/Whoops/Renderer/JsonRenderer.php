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
     */
    public function handle(): int
    {
        $exception = $this->getException();

        $inspector = new Inspector($exception);
        $this->setInspector($inspector);

        /** @var bool $addTrace */
        $addTrace = $this->addTraceToOutput();

        $error = $this->getExceptionData($inspector, $addTrace);

        $jsonEncodeOptions = static::JSON_ENCODE_OPTIONS
            | (\PHP_VERSION_ID >= 70400 ? \JSON_THROW_ON_ERROR : \JSON_PARTIAL_OUTPUT_ON_ERROR);

        echo \json_encode($error, $jsonEncodeOptions);

        return Handler::QUIT;
    }
}
