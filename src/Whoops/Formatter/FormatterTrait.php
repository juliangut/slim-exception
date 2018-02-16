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

use Jgut\HttpException\HttpException;
use Jgut\Slim\Exception\Whoops\Inspector;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Whoops dumper helper trait.
 */
trait FormatterTrait
{
    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function formatException(HttpException $exception, ServerRequestInterface $request): string
    {
        throw new \LogicException(\sprintf('Calling %s::formatException is not possible', __CLASS__));
    }

    /**
     * Get array data from exception.
     *
     * @param Inspector $inspector
     * @param bool      $addTrace
     *
     * @return array
     */
    protected function getExceptionData(Inspector $inspector, bool $addTrace = false): array
    {
        /** @var \Jgut\HttpException\HttpException $exception */
        $exception = $inspector->getException();

        $error = [
            'id' => $exception->getIdentifier(),
            'type' => \get_class($exception),
            'message' => $exception->getMessage(),
            'description' => $exception->getDescription(),
        ];

        if ($addTrace) {
            $error['trace'] = $this->getExceptionStack($inspector);
        }

        return $error;
    }

    /**
     * Get exception stack trace.
     *
     * @param Inspector $inspector
     *
     * @return array
     */
    protected function getExceptionStack(Inspector $inspector): array
    {
        $frameList = $inspector->getTraceFrames();

        $stackFrames = [];
        /** @var \Whoops\Exception\Frame $frame */
        foreach ($frameList as $frame) {
            $stackFrames[] = [
                'file' => $frame->getFile(true),
                'line' => $frame->getLine(),
                'function' => $frame->getFunction(),
                'class' => $frame->getClass(),
                'args' => $frame->getArgs(),
            ];
        }

        return $stackFrames;
    }
}
