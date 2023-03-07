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

namespace Jgut\Slim\Exception\Tests\Stubs;

use Jgut\Slim\Exception\Whoops\Inspector;
use Whoops\Exception\Frame;
use Whoops\Exception\FrameCollection;

class InspectorStub extends Inspector
{
    public function getFrames(array $frameFilters = []): FrameCollection
    {
        $frames = new FrameCollection([]);
        $frames->prependFrames(array_filter(
            parent::getFrames()->getArray(),
            static function (Frame $frame): bool {
                // Filter out PHPUnit from stack trace
                return mb_strpos($frame->getClass() ?? '', 'PHPUnit\\') !== 0
                    && mb_strpos($frame->getFile() ?? '', '/vendor/bin/phpunit') === false;
            },
        ));

        foreach ($frameFilters as $filterCallback) {
            $frames->filter($filterCallback);
        }

        return $frames;
    }
}
