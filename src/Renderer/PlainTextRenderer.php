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

use Throwable;

class PlainTextRenderer extends AbstractRenderer
{
    /**
     * @inheritDoc
     */
    public function __invoke(Throwable $exception, bool $displayErrorDetails): string
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

    private function formatException(Throwable $exception): string
    {
        $outputString = <<<'OUTPUT'
        Type: %s
        Code: %s
        Message: %s
        File: %s
        Line: %s
        Trace: %s
        OUTPUT;

        return sprintf(
            $outputString,
            \get_class($exception),
            $exception->getCode(),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString(),
        );
    }
}
