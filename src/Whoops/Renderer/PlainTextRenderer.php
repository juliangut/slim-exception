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
use Whoops\Handler\PlainTextHandler;

/**
 * Whoops custom plain text exception renderer.
 */
class PlainTextRenderer extends PlainTextHandler
{
    use RendererTrait;

    /**
     * {@inheritdoc}
     */
    public function __construct($logger = null)
    {
        parent::__construct($logger);

        $this->addTraceFunctionArgsToOutput(true);
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function generateResponse(): string
    {
        $exception = $this->getException();

        $inspector = new Inspector($exception);
        $this->setInspector($inspector);

        /** @var bool $addTrace */
        $addTrace = $this->addTraceToOutput();

        $error = $this->getExceptionData($inspector, $addTrace);
        $stackTrace = $addTrace ? "\n" . $this->getStackTraceOutput($error['trace']) : '';

        $type = $addTrace ? $error['type'] . ': ' : '';

        return \sprintf("%s%s%s\n", $type, $error['message'], $stackTrace);
    }

    /**
     * Get plain text stack trace.
     *
     * @param mixed[] $stackFrames
     *
     * @return string
     */
    protected function getStackTraceOutput(array $stackFrames): string
    {
        $line = 1;
        $stackTrace = \array_map(
            function (array $stack) use (&$line): string {
                $template = "\n%3d. %s->%s() %s:%d%s";
                if (!$stack['class']) {
                    $template = "\n%3d. %s%s() %s:%d%s";
                }

                $trace = \sprintf(
                    $template,
                    $line,
                    $stack['class'],
                    $stack['function'],
                    $stack['file'],
                    $stack['line'],
                    $this->getArguments($stack['args'], $line)
                );

                $line++;

                return $trace;
            },
            $stackFrames
        );

        return "Stack trace:\n" . \implode('', $stackTrace);
    }

    /**
     * Get call arguments.
     *
     * @param mixed[] $args
     * @param int     $line
     *
     * @return string
     */
    protected function getArguments(array $args, int $line): string
    {
        $addArgs = $this->addTraceFunctionArgsToOutput();
        if ($addArgs === false || $addArgs < $line) {
            // @codeCoverageIgnoreStart
            return '';
            // @codeCoverageIgnoreEnd
        }

        $argsOutputLimit = $this->getTraceFunctionArgsOutputLimit();

        \ob_start();

        \var_dump($args);

        if (\ob_get_length() > $argsOutputLimit) {
            // The argument var_dump is to big.
            // Discarded to limit memory usage.
            \ob_end_clean();

            return \sprintf(
                "\n%sArguments dump length greater than %d Bytes. Discarded.",
                parent::VAR_DUMP_PREFIX,
                $argsOutputLimit
            );
        }

        return \sprintf(
            "\n%s",
            \preg_replace('/^/m', parent::VAR_DUMP_PREFIX, (string) \ob_get_clean())
        );
    }
}
