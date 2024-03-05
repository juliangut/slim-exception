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

class HtmlRenderer extends AbstractRenderer
{
    public function __invoke(Throwable $exception, bool $displayErrorDetails): string
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
        <!doctype html>
        <html lang="en">
            <head>
                <meta charset="utf-8">
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

                <title>{$title}</title>

                <style>
                    body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana,sans-serif}
                    h1{margin:0;font-size:48px;font-weight:normal;line-height:48px}
                    strong{display:inline-block;width:65px}
                </style>
            </head>
            <body>
                <h1>{$title}</h1>
                <div>{$content}</div>
                <a href="#" onClick="window.history.go(-1)">Go Back</a>
            </body>
        </html>
        OUTPUT;
    }

    private function formatException(Throwable $exception): string
    {
        $outputString = <<<'OUTPUT'
        <div><strong>Type:</strong> %s</div>
        <div><strong>Code:</strong> %s</div>
        <div><strong>Message:</strong> %s</div>
        <div><strong>File:</strong> %s</div>
        <div><strong>Line:</strong> %s</div>
        <h2>Trace</h2>
        <pre>%s</pre>
        OUTPUT;

        return sprintf(
            $outputString,
            $exception::class,
            $exception->getCode(),
            htmlentities($exception->getMessage()),
            $exception->getFile(),
            $exception->getLine(),
            htmlentities($exception->getTraceAsString()),
        );
    }
}
