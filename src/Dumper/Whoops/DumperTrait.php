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

use Whoops\Exception\FrameCollection;
use Whoops\Exception\Inspector;

/**
 * Whoops dumper helper trait.
 */
trait DumperTrait
{
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
        /* @var \Jgut\Slim\Exception\HttpException $exception */
        $exception = $inspector->getException();

        $error = [
            'id' => $exception->getIdentifier(),
            'type' => get_class($exception),
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
     * @return array
     */
    protected function getExceptionStack(Inspector $inspector): array
    {
        $frames = $this->filterInternalFrames($inspector->getFrames());

        $exceptionStack = [];
        foreach ($frames as $frame) {
            $exceptionStack[] = [
                'file' => $frame->getFile(),
                'line' => $frame->getLine(),
                'function' => $frame->getFunction(),
                'class' => $frame->getClass(),
                'args' => $frame->getArgs(),
            ];
        }

        return $exceptionStack;
    }

    /**
     * Filter frames to remove HTTP Exception management classes.
     *
     * @param FrameCollection $frames
     *
     * @return FrameCollection
     */
    protected function filterInternalFrames(FrameCollection $frames): FrameCollection
    {
        /* @var \Whoops\Exception\Frame[] $frameList */
        $frameList = $frames->getArray();
        $excludedPathRegex = sprintf('!^%s/!', dirname(__DIR__, 2));

        $nonInternalStart = null;
        for ($i = 0, $length = count($frameList); $i < $length; $i++) {
            if (preg_match($excludedPathRegex, $frameList[$i]->getFile()) === 0) {
                $nonInternalStart = $i;
                break;
            }
        }

        if ($nonInternalStart !== null) {
            $frames = new FrameCollection([]);
            $frames->prependFrames(array_values(array_slice($frameList, $nonInternalStart)));
        }

        return $frames;
    }
}
