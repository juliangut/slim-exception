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

namespace Jgut\Slim\Exception\Handler\Whoops;

use Fig\Http\Message\StatusCodeInterface;
use Jgut\Slim\Exception\HttpException;
use Whoops\Exception\FrameCollection;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Util\Misc;

/**
 * Whoops custom HTML response handler.
 *
 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
 */
class HtmlHandler extends PrettyPageHandler
{
    use DumperTrait;

    /**
     * Get exception code.
     *
     * @return string
     */
    protected function getExceptionCode(): string
    {
        /* @var HttpException $exception */
        $exception = $this->getException();

        while ($exception instanceof HttpException
            && $exception->getHttpStatusCode() === StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR
            && $exception->getPrevious() !== null) {
            $exception = $exception->getPrevious();
        }

        $code = $exception->getCode();
        if ($exception instanceof \ErrorException) {
            // ErrorExceptions wrap the php-error types within the 'severity' property
            $code = Misc::translateErrorCode($exception->getSeverity());
        }

        return (string) $code;
    }

    /**
     * Detect frames that belong to the application.
     *
     * @return FrameCollection
     */
    protected function getExceptionFrames(): FrameCollection
    {
        $frames = $this->filterInternalFrames($this->getInspector()->getFrames());

        if ($this->getApplicationPaths()) {
            foreach ($frames as $frame) {
                foreach ($this->getApplicationPaths() as $path) {
                    if (strpos($frame->getFile(), $path) === 0) {
                        $frame->setApplication(true);
                        break;
                    }
                }
            }
        }

        return $frames;
    }
}
