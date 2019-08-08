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
use Whoops\Exception\FrameCollection;
use Whoops\Handler\PrettyPageHandler;

/**
 * Whoops custom HTML HTTP exception renderer.
 *
 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
 */
class HtmlRenderer extends PrettyPageHandler
{
    use RendererTrait;

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $exception = $this->getException();
        $this->setInspector(new Inspector($exception));

        return parent::handle();
    }

    /**
     * {@inheritdoc}
     */
    protected function getExceptionFrames(): FrameCollection
    {
        /** @var Inspector $inspector */
        $inspector = $this->getInspector();
        $frames = $inspector->getTraceFrames();

        $applicationPaths = $this->getApplicationPaths() ?? [];
        if (\count($applicationPaths) > 0) {
            foreach ($frames as $frame) {
                $filePath = $frame->getFile();

                foreach ($applicationPaths as $path) {
                    if (\strpos($filePath, $path) === 0) {
                        $frame->setApplication(true);
                        break;
                    }
                }
            }
        }

        return $frames;
    }
}
