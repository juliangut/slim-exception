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
use Psr\Log\LoggerInterface;
use Whoops\Handler\PlainTextHandler;

class PlainTextRenderer extends PlainTextHandler
{
    use RendererTrait;

    public function __construct(string $defaultTitle = 'Slim Application error', ?LoggerInterface $logger = null)
    {
        parent::__construct($logger);

        $this->defaultTitle = $defaultTitle;

        $this->addTraceFunctionArgsToOutput(true);
    }

    public function generateResponse(): string
    {
        $exception = $this->getException();

        $inspector = new Inspector($exception);
        $this->setInspector($inspector);

        /** @var bool $addTrace */
        $addTrace = $this->addTraceToOutput();

        $error = $this->getExceptionData($inspector, $addTrace);
        $stackTrace = $addTrace ? "\n" . $this->getStackTraceOutput($error['trace'] ?? []) : '';

        $type = $addTrace ? ($error['type'] ?? '') . ': ' : '';
        $message = $error['message'];

        return sprintf("%s%s%s\n", $type, $message, $stackTrace);
    }

    /**
     * @param array<TraceLine> $stackFrames
     */
    protected function getStackTraceOutput(array $stackFrames): string
    {
        $argsOutputLimit = $this->getTraceFunctionArgsOutputLimit();

        $line = 1;
        $stackTrace = array_map(
            function (array $stack) use ($argsOutputLimit, &$line): string {
                $trace = sprintf(
                    $stack['class'] !== null ? "\n%3d. %s%s() %s:%d%s" : "\n%3d. %s->%s() %s:%d%s",
                    $line,
                    $stack['class'],
                    $stack['function'],
                    $stack['file'],
                    $stack['line'],
                    $this->getArguments($stack['args'], $line, $argsOutputLimit),
                );

                ++$line;

                return $trace;
            },
            $stackFrames,
        );

        return "Stack trace:\n" . implode('', $stackTrace);
    }

    /**
     * @param array<string, mixed> $args
     */
    protected function getArguments(array $args, int $line, int $argsOutputLimit): string
    {
        $addArgs = $this->addTraceFunctionArgsToOutput();
        if ($addArgs === false || $addArgs < $line) {
            // @codeCoverageIgnoreStart
            return '';
            // @codeCoverageIgnoreEnd
        }

        ob_start();

        foreach ($this->flattenArguments($args) as $arg) {
            $this->dump($arg);
        }

        if (ob_get_length() > $argsOutputLimit) {
            // The argument var_dump is too big.
            // Discarded to limit memory usage.
            ob_end_clean();

            return sprintf(
                "\n%sArguments list dump length greater than %d Bytes. Discarded.",
                parent::VAR_DUMP_PREFIX,
                $argsOutputLimit,
            );
        }

        $argumentsTrace = (string) ob_get_clean();

        return $argumentsTrace === ''
            ? ''
            : sprintf("\n%s", preg_replace('/^/m', parent::VAR_DUMP_PREFIX, $argumentsTrace));
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    protected function flattenArguments(array $args): array
    {
        return array_map(
            function ($arg) {
                if (\is_object($arg)) {
                    $class = \get_class($arg);

                    return $class . (mb_strpos($class, 'class@anonymous') !== 0 ? '::class' : '');
                }

                if (\is_resource($arg)) {
                    return 'resource';
                }

                if (\is_array($arg)) {
                    /** @var array<string, mixed> $arg */
                    return $this->flattenArguments($arg);
                }

                return $arg;
            },
            $args,
        );
    }
}
