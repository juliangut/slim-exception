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

namespace Jgut\Slim\Exception\Dumper\Whoops;

use Whoops\Handler\Handler;
use Whoops\Handler\JsonResponseHandler;

/**
 * Whoops custom JSON response handler.
 */
class JsonHandler extends JsonResponseHandler
{
    use DumperTrait;

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
    public function handle(): int
    {
        /* @var bool $addTrace */
        $addTrace = $this->addTraceToOutput();

        $error = $this->getExceptionData($this->getInspector(), $addTrace);
        $options = JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT;

        echo json_encode(['error' => $error], $options);

        return Handler::QUIT;
    }
}
