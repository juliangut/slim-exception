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

namespace Jgut\Slim\Exception\Whoops\Renderer;

use Jgut\Slim\Exception\Whoops\Inspector;
use Whoops\Exception\FrameCollection;
use Whoops\Handler\Handler;
use Whoops\Handler\PrettyPageHandler;

/**
 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
 */
class HtmlRenderer extends PrettyPageHandler
{
    use RendererTrait;

    public function __construct(string $defaultTitle = 'Slim Application error')
    {
        parent::__construct();

        $this->defaultTitle = $defaultTitle;
        $this->setPageTitle($defaultTitle);
    }

    /**
     * @inheritDoc
     */
    public function handle()
    {
        $exception = $this->getException();
        $this->setInspector(new Inspector($exception));

        return parent::handle() ?? Handler::QUIT;
    }

    /**
     * @inheritDoc
     */
    protected function getExceptionFrames(): FrameCollection
    {
        /** @var Inspector $inspector */
        $inspector = $this->getInspector();
        $frames = $inspector->getTraceFrames();

        /** @var array<string>|null $applicationPaths */
        $applicationPaths = $this->getApplicationPaths();
        $applicationPaths ??= [];

        if (\count($applicationPaths) > 0) {
            foreach ($frames as $frame) {
                $filePath = $frame->getFile();

                foreach ($applicationPaths as $path) {
                    if (mb_strpos($filePath, $path) === 0) {
                        $frame->setApplication(true);
                        break;
                    }
                }
            }
        }

        return $frames;
    }
}
