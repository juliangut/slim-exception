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

namespace Jgut\Slim\Exception\Whoops\Formatter;

use Jgut\Slim\Exception\HttpExceptionFormatter;
use Jgut\Slim\Exception\Whoops\Inspector;
use Whoops\Handler\Handler;
use Whoops\Handler\JsonResponseHandler;

/**
 * Whoops custom JSON HTTP exception formatter.
 */
class Json extends JsonResponseHandler implements HttpExceptionFormatter
{
    use FormatterTrait;

    /**
     * JsonHandler constructor.
     */
    public function __construct()
    {
        $this->addTraceToOutput(true);
    }

    /**
     * {@inheritdoc}
     */
    public function getContentTypes(): array
    {
        return [
            'application/json',
            'text/json',
            'application/x-json',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function handle(): int
    {
        /* @var \Jgut\Slim\Exception\HttpException $exception */
        $exception = $this->getException();

        $inspector = new Inspector($exception);
        $this->setInspector($inspector);

        /* @var bool $addTrace */
        $addTrace = $this->addTraceToOutput();

        $error = $this->getExceptionData($inspector, $addTrace);
        $options = JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT;

        echo json_encode(['error' => $error], $options);

        return Handler::QUIT;
    }
}
