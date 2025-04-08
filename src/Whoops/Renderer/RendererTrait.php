<?php

/*
 * (c) 2017-2025 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-exception
 */

declare(strict_types=1);

namespace Jgut\Slim\Exception\Whoops\Renderer;

use Slim\Exception\HttpException;
use Throwable;
use Whoops\Exception\Formatter;
use Whoops\Exception\Frame;
use Whoops\Exception\Inspector as WhoopsInspector;
use Whoops\Handler\Handler;
use Whoops\Inspector\InspectorInterface;
use Whoops\RunInterface;

trait RendererTrait
{
    /**
     * @param list<callable(Frame): bool> $frameFilters
     *
     * @return ExceptionData
     */
    protected function getExceptionData(
        InspectorInterface $inspector,
        bool $shouldAddTrace,
        array $frameFilters,
    ): array {
        $exception = $inspector->getException();

        /** @var ExceptionData $error */
        $error = Formatter::formatExceptionAsDataArray(
            $inspector,
            $shouldAddTrace,
            $frameFilters,
        );
        $error['message'] = $exception instanceof HttpException ? $exception->getTitle() : $this->defaultTitle;

        return $error;
    }

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
