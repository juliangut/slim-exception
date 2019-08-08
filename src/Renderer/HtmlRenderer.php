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

namespace Jgut\Slim\Exception\Renderer;

use Slim\Interfaces\ErrorRendererInterface;

/**
 * HTML HTTP exception renderer.
 */
class HtmlRenderer implements ErrorRendererInterface
{
    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $message = '';

    /**
     * HTML HTTP exception renderer constructor.
     *
     * @param string $title
     * @param string $message
     */
    public function __construct(
        string $title = 'Application error',
        string $message = 'An application error has occurred. Sorry for the temporary inconvenience'
    ) {
        $this->title = $title;
        $this->message = $message;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(\Throwable $exception, bool $displayErrorDetails): string
    {
        return \sprintf(
            '<!DOCTYPE html><html lang="en"><head><meta http-equiv="Content-Type" content="text/html; ' .
            'charset=utf-8"><title>%1$s</title><style>body{margin:0;padding:30px;font:12px/1.5 ' .
            'Helvetica,Arial,Verdana,sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;' .
            '}h1 span{font-size:.3em}</style></head><body><h1>%1$s <span>(%2$s)</span></h1><p>%3$s</p></body></html>',
            $this->title,
            0, // TODO $exception->getIdentifier(),
            $this->message
        );
    }
}
