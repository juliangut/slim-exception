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
 * HTML exception renderer.
 */
class HtmlRenderer extends AbstractRenderer
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(\Throwable $exception, bool $displayErrorDetails): string
    {
        $title = $this->getErrorTitle($exception);
        if ($displayErrorDetails) {
            $content = <<<CONTENT
<p>The application could not run because of the following error:</p>
<h2>Details</h2>
{$this->formatException($exception)}
CONTENT;
        } else {
            $content = '<p>' . $this->getErrorDescription($exception) . '</p>';
        }

        return <<<OUTPUT
<html lang="en">' .
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>$title</title>
        <style>
            body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana,sans-serif}
            h1{margin:0;font-size:48px;font-weight:normal;line-height:48px}
            strong{display:inline-block;width:65px}
        </style>
    </head>
    <body>
        <h1>$title</h1>
        <div>$content</div>
        <a href="#" onClick="window.history.go(-1)">Go Back</a>
    </body>
</html>
OUTPUT;
    }

    /**
     * @param \Throwable $exception
     *
     * @return string
     */
    private function formatException(\Throwable $exception): string
    {
        $output = \sprintf('<div><strong>Type:</strong> %s</div>', \get_class($exception));

        $code = $exception->getCode();
        if ($code !== null) {
            $output .= \sprintf('<div><strong>Code:</strong> %s</div>', $code);
        }

        $message = $exception->getMessage();
        if ($message !== null) {
            $output .= \sprintf('<div><strong>Message:</strong> %s</div>', \htmlentities($message));
        }

        $file = $exception->getFile();
        if ($file !== null) {
            $output .= \sprintf('<div><strong>File:</strong> %s</div>', $file);
        }

        $line = $exception->getLine();
        if ($line !== null) {
            $output .= \sprintf('<div><strong>Line:</strong> %s</div>', $line);
        }

        $trace = $exception->getTraceAsString();
        if ($trace !== null) {
            $output .= '<h2>Trace</h2>';
            $output .= \sprintf('<pre>%s</pre>', \htmlentities($trace));
        }

        return $output;
    }
}
