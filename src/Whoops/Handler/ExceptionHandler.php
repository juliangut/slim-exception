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

namespace Jgut\Slim\Exception\Whoops\Handler;

use Jgut\HttpException\HttpException;
use Jgut\Slim\Exception\ExceptionFormatter;
use Jgut\Slim\Exception\Handler\ExceptionHandler as BaseExceptionHandler;
use Jgut\Slim\Exception\Whoops\Formatter\Html;
use Negotiation\Negotiator;
use Psr\Http\Message\ServerRequestInterface;
use Whoops\Handler\HandlerInterface;
use Whoops\Run as Whoops;

/**
 * Debug exception handler.
 */
class ExceptionHandler extends BaseExceptionHandler
{
    const REQUEST_DATA_TABLE_LABEL = 'Slim Application (Request)';

    /**
     * Whoops runner.
     *
     * @var Whoops
     */
    protected $whoops;

    /**
     * Abstract errors handler constructor.
     *
     * @param Negotiator $negotiator
     * @param Whoops     $whoops
     *
     * @throws \RuntimeException
     */
    public function __construct(Negotiator $negotiator, Whoops $whoops)
    {
        parent::__construct($negotiator);

        $whoops = clone $whoops;
        $whoops->clearHandlers();
        $whoops->allowQuit(false);
        $whoops->writeToOutput(false);

        $this->whoops = $whoops;
    }

    /**
     * Add exception formatter.
     *
     * @param ExceptionFormatter   $formatter
     * @param string|string[]|null $contentTypes
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function addFormatter(ExceptionFormatter $formatter, $contentTypes = null)
    {
        if (!$formatter instanceof HandlerInterface) {
            throw new \InvalidArgumentException(\sprintf(
                'Formatter %s for Whoops handler does not implement %s',
                \get_class($formatter),
                HandlerInterface::class
            ));
        }

        parent::addFormatter($formatter, $contentTypes);
    }

    /**
     * {@inheritdoc}
     */
    protected function getExceptionOutput(
        string $contentType,
        HttpException $exception,
        ServerRequestInterface $request
    ): string {
        $formatter = $this->formatters[$contentType];
        if ($formatter instanceof Html || \is_subclass_of($formatter, Html::class)) {
            $formatter = $this->addRequestData($formatter, $request);
        }

        $this->whoops->pushHandler($formatter);

        $output = $this->whoops->handleException($exception);

        $this->whoops->clearHandlers();

        return $output;
    }

    /**
     * Add extra data table with request information.
     *
     * @param Html                   $formatter
     * @param ServerRequestInterface $request
     *
     * @return Html
     */
    protected function addRequestData(Html $formatter, ServerRequestInterface $request): Html
    {
        if (empty($formatter->getDataTables(self::REQUEST_DATA_TABLE_LABEL))) {
            $formatter->addDataTable(
                self::REQUEST_DATA_TABLE_LABEL,
                [
                    'Accept Charset' => $request->getHeader('Accept') ?: '<none>',
                    'Content Charset' => $request->getHeader('Content-Type') ?: '<none>',
                    'Path' => $request->getUri()->getPath(),
                    'Query String' => $request->getUri()->getQuery() ?: '<none>',
                    'HTTP Method' => $request->getMethod(),
                    'Base URL' => (string) $request->getUri(),
                    'Scheme' => $request->getUri()->getScheme(),
                    'Port' => $request->getUri()->getPort(),
                    'Host' => $request->getUri()->getHost(),
                ]
            );
        }

        return $formatter;
    }
}
