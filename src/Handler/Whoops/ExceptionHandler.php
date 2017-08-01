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

use Jgut\Slim\Exception\Handler\AbstractHttpExceptionHandler;
use Jgut\Slim\Exception\HttpException;
use Psr\Http\Message\RequestInterface;
use Whoops\Handler\Handler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run as Whoops;

/**
 * Debug exception handler.
 */
class ExceptionHandler extends AbstractHttpExceptionHandler
{
    /**
     * Whoops runner.
     *
     * @var Whoops
     */
    protected $whoops;

    /**
     * Handlers list.
     *
     * @var Handler[][]|PrettyPageHandler[][] hack for PHPStan
     */
    protected $handlers = [];

    /**
     * Abstract errors handler constructor.
     *
     * @param Whoops $whoops
     *
     * @throws \RuntimeException
     */
    public function __construct(Whoops $whoops)
    {
        foreach ($whoops->getHandlers() as $handler) {
            $this->addHandler($handler);
        }

        $whoops->clearHandlers();
        $whoops->allowQuit(false);
        $whoops->writeToOutput(false);

        $this->whoops = $whoops;
    }

    /**
     * Add whoops handler.
     *
     * @param Handler              $handler
     * @param string|string[]|null $contentTypes
     *
     * @throws \RuntimeException
     */
    public function addHandler(Handler $handler, $contentTypes = null)
    {
        if ($contentTypes === null && method_exists($handler, 'contentType')) {
            $contentTypes = array_filter([call_user_func([$handler, 'contentType'])]);
        }

        if (!is_array($contentTypes)) {
            $contentTypes = [$contentTypes];
        }

        $contentTypes = array_filter(
            $contentTypes,
            function ($contentType) {
                return is_string($contentType);
            }
        );

        if (!count($contentTypes)) {
            throw new \RuntimeException(sprintf('No content type defined for %s handler', get_class($handler)));
        }

        foreach ($contentTypes as $contentType) {
            if (!array_key_exists($contentType, $this->handlers)) {
                $this->handlers[$contentType] = [];
            }

            $this->handlers[$contentType][] = $handler;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getContentTypes(): array
    {
        return array_keys($this->handlers);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function getExceptionOutput(
        string $contentType,
        HttpException $exception,
        RequestInterface $request
    ): string {
        if (!in_array($contentType, $this->getContentTypes())) {
            throw new \RuntimeException(sprintf('There is no defined handler for content type "%s"', $contentType));
        }

        foreach ($this->handlers[$contentType] as $handler) {
            if ($handler instanceof PrettyPageHandler || is_subclass_of($handler, PrettyPageHandler::class)) {
                $handler = $this->addRequestData($handler, $request);
            }

            $this->whoops->pushHandler($handler);
        }

        $output = $this->whoops->handleException($exception);

        $this->whoops->clearHandlers();

        return $output;
    }

    /**
     * Add extra data table with request information.
     *
     * @param PrettyPageHandler $handler
     * @param RequestInterface  $request
     *
     * @return PrettyPageHandler
     */
    protected function addRequestData(PrettyPageHandler $handler, RequestInterface $request): PrettyPageHandler
    {
        static $dataTableLabel = 'Slim Application (Request)';

        if (empty($handler->getDataTables($dataTableLabel))) {
            $handler->addDataTable('Slim Application (Request)', [
                'Accept Charset' => $request->getHeader('Accept') ?: '<none>',
                'Content Charset' => $request->getHeader('Content-Type') ?: '<none>',
                'Path' => $request->getUri()->getPath(),
                'Query String' => $request->getUri()->getQuery() ?: '<none>',
                'HTTP Method' => $request->getMethod(),
                'Base URL' => (string) $request->getUri(),
                'Scheme' => $request->getUri()->getScheme(),
                'Port' => $request->getUri()->getPort(),
                'Host' => $request->getUri()->getHost(),
            ]);
        }

        return $handler;
    }
}
