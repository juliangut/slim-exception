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
use Slim\Exception\HttpException;
use Throwable;
use Whoops\Exception\Frame;
use Whoops\Exception\Inspector as WhoopsInspector;
use Whoops\Handler\Handler;
use Whoops\RunInterface;

trait RendererTrait
{
    protected string $defaultTitle;

    /**
     * Get array data from exception.
     *
     * @return array{message: string, type?: class-string<Throwable>, trace?: array}
     */
    protected function getExceptionData(Inspector $inspector, bool $addTrace = false): array
    {
        $exception = $inspector->getException();

        $error = [
            'message' => $exception instanceof HttpException ? $exception->getTitle() : $this->defaultTitle,
        ];

        if ($addTrace) {
            $error['type'] = \get_class($exception);
            $error['trace'] = $this->getExceptionStack($inspector);
        }

        return $error;
    }

    /**
     * Get exception stack trace.
     *
     * @return array<int, array{file: ?string, line: ?int, function: ?string, class: ?string, args: array}>
     */
    protected function getExceptionStack(Inspector $inspector): array
    {
        $frameList = $inspector->getTraceFrames();

        $stackFrames = [];
        /** @var Frame $frame */
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
     */
    final public function __invoke(Throwable $exception, WhoopsInspector $inspector, RunInterface $run): int
    {
        $this->setException($exception);
        $this->setInspector($inspector);
        $this->setRun($run);

        return $this->handle() ?? Handler::DONE;
    }

    abstract public function setException(Throwable $exception);

    abstract public function setInspector(WhoopsInspector $inspector);

    abstract public function setRun(RunInterface $run);

    /**
     * @return int
     */
    abstract public function handle();
}
