<?php

/*
 * slim-exception (https://github.com/juliangut/slim-exception).
 * Slim exception handling.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-exception
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

declare(strict_types=1);

namespace Jgut\Slim\Exception\Whoops;

use Whoops\Exception\FrameCollection;
use Whoops\Exception\Inspector as BaseInspector;

/**
 * Custom Whoops exception inspector.
 */
class Inspector extends BaseInspector
{
    /**
     * exception inspector constructor.
     *
     * @param \Throwable $exception
     */
    public function __construct(\Throwable $exception)
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
        $frames = new FrameCollection([]);
        $frames->prependFrames($this->filterTraceFrames());

        return $frames;
    }

    /**
     * Filter stack frame list.
     * Remove internal frames.
     *
     * @return \Whoops\Exception\Frame[]
     */
    protected function filterTraceFrames(): array
    {
        /** @var \Whoops\Exception\Frame[] $frameList */
        $frameList = $this->getFrames()->getArray();

        $excludedPathRegex = \sprintf('!^%s/.+\.php$!', \dirname(__DIR__, 2));

        $firstFrame = 0;
        for ($i = 0, $length = \count($frameList); $i < $length; $i++) {
            if (\preg_match($excludedPathRegex, $frameList[$i]->getFile() ?? '') === 1) {
                continue;
            }

            $firstFrame = $i;
            break;
        }

        return \array_values(\array_slice($frameList, $firstFrame));
    }
}
