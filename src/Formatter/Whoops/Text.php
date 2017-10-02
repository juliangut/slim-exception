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

namespace Jgut\Slim\Exception\Formatter\Whoops;

use Jgut\Slim\Exception\HttpExceptionFormatter;
use Whoops\Handler\PlainTextHandler;

/**
 * Whoops custom plain text HTTP exception formatter.
 */
class Text extends PlainTextHandler implements HttpExceptionFormatter
{
    use FormatterTrait;

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
     */
    public function getContentTypes(): array
    {
        return [
            'text/plain',
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function generateResponse(): string
    {
        /* @var bool $addTrace */
        $addTrace = $this->addTraceToOutput();

        $error = $this->getExceptionData($this->getInspector(), $addTrace);

        return sprintf(
            "(%s) %s: %s%s\n",
            $error['id'],
            $error['type'],
            $error['message'],
            $this->getStack()
        );
    }

    /**
     * Get call stack.
     *
     * @return string
     */
    protected function getStack(): string
    {
        if (!$this->addTraceToOutput()) {
            return '';
        }

        $argumentsDumper = [$this, 'getArguments'];

        $line = 1;
        $stack = array_map(
            function (array $stack) use ($argumentsDumper, &$line) {
                $template = "\n%3d. %s->%s() %s:%d%s";
                if (!$stack['class']) {
                    $template = "\n%3d. %s%s() %s:%d%s";
                }

                $trace = sprintf(
                    $template,
                    $line,
                    $stack['class'],
                    $stack['function'],
                    $stack['file'],
                    $stack['line'],
                    $argumentsDumper($stack['args'], $line)
                );

                $line++;

                return $trace;
            },
            $this->getExceptionStack($this->getInspector())
        );

        return "\nStack trace:" . implode('', $stack);
    }

    /**
     * Get call arguments.
     *
     * @param array $args
     * @param int   $line
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

        ob_start();

        var_dump($args);

        if (ob_get_length() > $this->getTraceFunctionArgsOutputLimit()) {
            // The argument var_dump is to big.
            // Discarded to limit memory usage.
            ob_end_clean();

            return sprintf(
                "\n%sArguments dump length greater than %d Bytes. Discarded.",
                parent::VAR_DUMP_PREFIX,
                $this->getTraceFunctionArgsOutputLimit()
            );
        }

        return sprintf(
            "\n%s",
            preg_replace('/^/m', parent::VAR_DUMP_PREFIX, ob_get_clean())
        );
    }
}
