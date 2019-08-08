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

namespace Jgut\Slim\Exception\Whoops\Renderer;

use Jgut\Slim\Exception\Whoops\Inspector;
use Whoops\Exception\Inspector as WhoopsInspector;
use Whoops\Handler\Handler;
use Whoops\RunInterface;

/**
 * Whoops dumper helper trait.
 */
trait RendererTrait
{
    /**
     * Get array data from exception.
     *
     * @param Inspector $inspector
     * @param bool      $addTrace
     *
     * @return mixed[]
     */
    protected function getExceptionData(Inspector $inspector, bool $addTrace = false): array
    {
        $exception = $inspector->getException();

        $error = [
            'id' => 0, // TODO $exception->getIdentifier(),
            'type' => \get_class($exception),
            'message' => $exception->getMessage(),
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
     * @return mixed[]
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

    /**
     * Callable wrapper.
     *
     * @param \Throwable   $exception
     * @param Inspector    $inspector
     * @param RunInterface $run
     *
     * @return int
     */
    final public function __invoke(\Throwable $exception, WhoopsInspector $inspector, RunInterface $run): int
    {
        $this->setException($exception);
        $this->setInspector($inspector);
        $this->setRun($run);

        return $this->handle() ?? Handler::DONE;
    }
}
