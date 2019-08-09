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

use Slim\Interfaces\ErrorRendererInterface;

/**
 * Plain text exception renderer.
 */
class PlainTextRenderer implements ErrorRendererInterface
{
    /**
     * @var string
     */
    protected $title = '';

    /**
     * Plain text exception renderer constructor.
     *
     * @param string $title
     */
    public function __construct(string $title = 'Application error')
    {
        $this->title = $title;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(\Throwable $exception, bool $displayErrorDetails): string
    {
        $output = $this->title . ': ' . $exception->getMessage();

        if ($displayErrorDetails) {
            $output .= "\nTrace:";

            do {
                $output .= "\n" . $this->renderException($exception);
            } while ($exception = $exception->getPrevious());
        }

        return $output;
    }

    /**
     * @param \Throwable $exception
     *
     * @return string
     */
    private function renderException(\Throwable $exception): string
    {
        $text = \sprintf("Type: %s\n", \get_class($exception));

        $code = $exception->getCode();
        if ($code !== null) {
            $text .= \sprintf("Code: %s\n", $code);
        }

        $message = $exception->getMessage();
        if ($message !== null) {
            $text .= \sprintf("Message: %s\n", \htmlentities($message));
        }

        $file = $exception->getFile();
        if ($file !== null) {
            $text .= \sprintf("File: %s\n", $file);
        }

        $line = $exception->getLine();
        if ($line !== null) {
            $text .= \sprintf("Line: %s\n", $line);
        }

        $trace = $exception->getTraceAsString();
        if ($trace !== null) {
            $text .= \sprintf('Trace: %s', $trace);
        }

        return $text;
    }
}
