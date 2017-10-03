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

use Jgut\Slim\Exception\HttpException;
use Jgut\Slim\Exception\HttpExceptionManager;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Whoops\Exception\FrameCollection;
use Whoops\Exception\Inspector;

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
        throw new \LogicException(sprintf('Calling %s::format is not possible', __CLASS__));
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
        /* @var \Jgut\Slim\Exception\HttpException $exception */
        $exception = $inspector->getException();

        $error = [
            'id' => $exception->getIdentifier(),
            'type' => get_class($exception),
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
        $firstNonInternal = $this->getFirstNonInternalFrame($frameList);

        $frames = new FrameCollection([]);
        $frames->prependFrames(array_values(array_slice($frameList, $firstNonInternal)));

        return $frames;
    }

    /**
     * Find position of the first non internal frame.
     *
     * @param \Whoops\Exception\Frame[] $frames
     *
     * @return int
     */
    protected function getFirstNonInternalFrame(array $frames): int
    {
        $excludedPathRegex = sprintf('!^%s/.+\.php$!', dirname(__DIR__, 2));
        $excludedClosureRegex = sprintf(
            '!^%s::(error|notFound|notAllowed)Handler$!',
            str_replace('\\', '\\\\', HttpExceptionManager::class)
        );

        $firstFrame = 0;
        for ($i = 0, $length = count($frames); $i < $length; $i++) {
            $frame = $frames[$i];
            $frameCallback = sprintf('%s::%s', $frame->getClass(), $frame->getFunction());

            if (preg_match($excludedClosureRegex, $frameCallback)
                || preg_match($excludedPathRegex, $frame->getFile())
            ) {
                continue;
            }

            if ($frameCallback === App::class . '::__invoke') {
                // notFoundHandler/notAllowedHandler directly called by \Slim\App::__invoke. Display manager handling
                $firstFrame = $i - 1;
                break;
            }

            if ($frame->getFile() === '[internal]') {
                // \Error or triggered errors (transformed into \ErrorException)
                $firstFrame = $i + 1;
                break;
            }

            if (isset($frames[$i + 1])) {
                $nextFrame = $frames[$i + 1];
                $nextFrameCallback = sprintf('%s::%s', $nextFrame->getClass(), $nextFrame->getFunction());

                if ($nextFrameCallback === App::class . '::handleException') {
                    // Exception captured by \Slim\App::handleException. Skip Slim's handling
                    $firstFrame = $i + 2;
                    break;
                }
            }

            $firstFrame = $i;
            break;
        }

        return $firstFrame;
    }
}
