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

namespace Jgut\Slim\Exception\Formatter;

use Jgut\HttpException\HttpException;
use Jgut\Slim\Exception\ExceptionFormatter;
use Psr\Http\Message\ServerRequestInterface;

/**
 * HTML HTTP exception formatter.
 */
class Html implements ExceptionFormatter
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
     * HTML HTTP exception formatter constructor.
     *
     * @param string $title
     * @param string $message
     */
    public function __construct(
        $title = 'Application error',
        $message = 'An application error has occurred. Sorry for the temporary inconvenience'
    ) {
        $this->title = $title;
        $this->message = $message;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentTypes(): array
    {
        return [
            'text/html',
            'application/xhtml+xml',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function formatException(HttpException $exception, ServerRequestInterface $request): string
    {
        return \sprintf(
            '<!DOCTYPE html><html lang="en"><head><meta http-equiv="Content-Type" content="text/html; ' .
            'charset=utf-8"><title>%1$s</title><style>body{margin:0;padding:30px;font:12px/1.5 ' .
            'Helvetica,Arial,Verdana,sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;' .
            '}h1 span{font-size:.3em}</style></head><body><h1>%1$s <span>(%2$s)</span></h1><p>%3$s</p></body></html>',
            $this->title,
            $exception->getIdentifier(),
            $this->message
        );
    }
}
