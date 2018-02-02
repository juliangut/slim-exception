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

namespace Jgut\Slim\Exception\Whoops;

use Jgut\Slim\Exception\HttpException;
use Jgut\Slim\Exception\HttpExceptionManager;
use Slim\App;
use Whoops\Exception\FrameCollection;
use Whoops\Exception\Inspector as BaseInspector;

/**
 * Custom Whoops HTTP Exception inspector.
 */
class Inspector extends BaseInspector
{
    /**
     * HTTP Exception inspector constructor.
     *
     * @param HttpException $exception
     */
    public function __construct(HttpException $exception)
    {
        parent::__construct($exception);
    }

    /**
     * Get stack trace frames.
     *
     * Exception handling frames are removed.
     *
     * @return FrameCollection
     */
    public function getTraceFrames(): FrameCollection
    {
        /** @var \Whoops\Exception\Frame[] $frameList */
        $frameList = $this->getFrames()->getArray();
        $firstNonInternal = $this->findFirstNonInternalFrame($frameList);

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
    protected function findFirstNonInternalFrame(array $frames): int
    {
        $excludedPathRegex = sprintf('!^%s/.+\.php$!', dirname(__DIR__));
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
