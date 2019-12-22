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
 * Plain text exception renderer.
 */
class PlainTextRenderer extends AbstractRenderer
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(\Throwable $exception, bool $displayErrorDetails): string
    {
        $output = $this->getErrorTitle($exception);

        if ($displayErrorDetails) {
            $output .= $this->formatException($exception);

            while ($exception = $exception->getPrevious()) {
                $output .= "\nPrevious Error:\n";
                $output .= $this->formatException($exception);
            }
        }

        return $output;
    }

    /**
     * @param \Throwable $exception
     *
     * @return string
     */
    private function formatException(\Throwable $exception): string
    {
        $output = \sprintf("Type: %s\n", \get_class($exception));

        $code = $exception->getCode();
        if ($code !== null) {
            $output .= \sprintf("Code: %s\n", $code);
        }

        $message = $exception->getMessage();
        if ($message !== null) {
            $output .= \sprintf("Message: %s\n", \htmlentities($message));
        }

        $file = $exception->getFile();
        if ($file !== null) {
            $output .= \sprintf("File: %s\n", $file);
        }

        $line = $exception->getLine();
        if ($line !== null) {
            $output .= \sprintf("Line: %s\n", $line);
        }

        $trace = $exception->getTraceAsString();
        if ($trace !== null) {
            $output .= \sprintf('Trace: %s', $trace);
        }

        return $output;
    }
}
