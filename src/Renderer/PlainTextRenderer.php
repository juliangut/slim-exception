<?php

/*
 * (c) 2017-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-exception
 */

declare(strict_types=1);

namespace Jgut\Slim\Exception\Renderer;

use Throwable;

class PlainTextRenderer extends AbstractRenderer
{
    public function __invoke(Throwable $exception, bool $displayErrorDetails): string
    {
        $output = $this->getErrorTitle($exception);

        if ($displayErrorDetails) {
            $output .= "\n" . $this->formatException($exception);

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
        Trace:
        %s
        OUTPUT;

        return sprintf(
            $outputString,
            $exception::class,
            $exception->getCode(),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString(),
        );
    }
}
