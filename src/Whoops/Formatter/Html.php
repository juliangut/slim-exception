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

use Jgut\Slim\Exception\HttpExceptionFormatter;
use Jgut\Slim\Exception\Whoops\Inspector;
use Whoops\Exception\FrameCollection;
use Whoops\Handler\PrettyPageHandler;

/**
 * Whoops custom HTML HTTP exception formatter.
 *
 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
 */
class Html extends PrettyPageHandler implements HttpExceptionFormatter
{
    use FormatterTrait;

    /**
     * {@inheritdoc}
     */
    public function getContentTypes(): array
    {
        return [
            'text/html',
            'application/xhtml+xml',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        /** @var \Jgut\Slim\Exception\HttpException $exception */
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

        if (is_array($this->getApplicationPaths()) && count($this->getApplicationPaths()) > 0) {
            foreach ($frames as $frame) {
                $filePath = $frame->getFile();

                foreach ($this->getApplicationPaths() as $path) {
                    if (strpos($filePath, $path) === 0) {
                        $frame->setApplication(true);
                        break;
                    }
                }
            }
        }

        return $frames;
    }
}
