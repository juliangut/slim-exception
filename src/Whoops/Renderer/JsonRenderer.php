<?php

/*
 * (c) 2017-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-exception
 */

declare(strict_types=1);

namespace Jgut\Slim\Exception\Whoops\Renderer;

use JsonException;
use RuntimeException;
use Whoops\Exception\Frame;
use Whoops\Handler\Handler;

class JsonRenderer extends Handler
{
    use RendererTrait;

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

    protected bool $returnFrames = true;

    public function __construct(
        protected string $defaultTitle = 'Slim Application error',
        private bool $jsonApi = false,
    ) {}

    public function setPrettify(bool $prettify): void
    {
        $this->prettify = $prettify;
    }

    public function addTraceToOutput(bool $returnFrames = null): bool
    {
        if ($returnFrames !== null) {
            $this->returnFrames = $returnFrames;
        }

        return $this->returnFrames;
    }

    /**
     * @throws RuntimeException
     */
    public function handle()
    {
        /** @var list<callable(Frame): bool> $frameFilters */
        $frameFilters = array_values($this->getRun()->getFrameFilters());

        if ($this->jsonApi === true) {
            $response = [
                'errors' => [
                    $this->getExceptionData($this->getInspector(), $this->addTraceToOutput(), $frameFilters),
                ],
            ];
        } else {
            $response = [
                'error' => [
                    $this->getExceptionData($this->getInspector(), $this->addTraceToOutput(), $frameFilters),
                ],
            ];
        }

        try {
            $output = json_encode($response, $this->getJsonFlags() | \JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException(
                self::JSON_ERROR_MESSAGES[$exception->getCode()] ?? 'Unknown error.',
                0,
                $exception,
            );
            // @codeCoverageIgnoreEnd
        }

        echo $output; // @phpstan-ignore-line

        return Handler::QUIT;
    }

    protected function getJsonFlags(): int
    {
        $jsonFlags = \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES | \JSON_PRESERVE_ZERO_FRACTION;
        if ($this->prettify) {
            $jsonFlags |= \JSON_PRETTY_PRINT;
        }

        return $jsonFlags;
    }
}
