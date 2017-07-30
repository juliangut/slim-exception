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

namespace Jgut\Slim\Exception\Handler;

use Fig\Http\Message\StatusCodeInterface;
use Jgut\Slim\Exception\Dumper\Dumper;
use Jgut\Slim\Exception\HttpException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;
use Slim\Http\Stream;

/**
 * Abstract HTTP exception handler.
 */
abstract class AbstractHttpExceptionHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * PHP to PSR3 error map.
     *
     * @var array
     */
    private $logLevelMap = [
        E_ERROR => LogLevel::ALERT,
        E_WARNING => LogLevel::WARNING,
        E_PARSE => LogLevel::ALERT,
        E_NOTICE => LogLevel::NOTICE,
        E_CORE_ERROR => LogLevel::ALERT,
        E_CORE_WARNING => LogLevel::WARNING,
        E_COMPILE_ERROR => LogLevel::ALERT,
        E_COMPILE_WARNING => LogLevel::WARNING,
        E_USER_ERROR => LogLevel::ERROR,
        E_USER_WARNING => LogLevel::WARNING,
        E_USER_NOTICE => LogLevel::NOTICE,
        E_STRICT => LogLevel::WARNING,
        E_RECOVERABLE_ERROR => LogLevel::ERROR,
        E_DEPRECATED => LogLevel::WARNING,
        E_USER_DEPRECATED => LogLevel::WARNING,
    ];

    /**
     * Content types being handled.
     * First on the list is considered default.
     *
     * @var string[]
     */
    protected $knownContentTypes = [
        'text/html',
        'application/xhtml+xml',
        'text/json',
        'application/json',
        'application/x-json',
        'text/xml',
        'application/xml',
        'application/x-xml',
        'text/plain',
    ];

    /**
     * Default content type.
     *
     * @var string
     */
    protected $defaultContentType = 'text/html';

    /**
     * HTTP exception dumper.
     *
     * @var Dumper|null
     */
    protected $dumper;

    /**
     * Add custom content type.
     *
     * @param string $contentType
     */
    public function addContentType(string $contentType)
    {
        $this->knownContentTypes[] = $contentType;

        $this->knownContentTypes = array_unique($this->knownContentTypes);
    }

    /**
     * Set default content type.
     *
     * @param string $contentType
     *
     * @throws \InvalidArgumentException
     */
    public function setDefaultContentType(string $contentType)
    {
        if (!in_array($contentType, $this->knownContentTypes)) {
            throw new \InvalidArgumentException(sprintf('%s is not among known content types', $contentType));
        }

        $this->defaultContentType = $contentType;
    }

    /**
     * Set exception dumper.
     *
     * @param Dumper $dumper
     */
    public function setDumper(Dumper $dumper)
    {
        $this->dumper = $dumper;
    }

    /**
     * Handle error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param HttpException     $exception
     *
     * @return ResponseInterface
     */
    protected function handleError(
        RequestInterface $request,
        ResponseInterface $response,
        HttpException $exception
    ): ResponseInterface {
        $this->log($request, $exception);

        if ($this->isCli()) {
            $request = $request->withHeader('Accept', 'text/plain');
        }
        $contentType = $this->determineContentType($request);

        $body = $this->getNewStream();
        $body->write($this->getErrorOutput($contentType, $exception, $request));

        return $response
            ->withStatus($exception->getHttpStatusCode())
            ->withHeader('Content-Type', $contentType . '; charset=utf-8')
            ->withBody($body);
    }

    /**
     * Get exception output.
     *
     * @param string           $contentType
     * @param HttpException    $exception
     * @param RequestInterface $request
     *
     * @return string
     */
    protected function getErrorOutput(string $contentType, HttpException $exception, RequestInterface $request): string
    {
        if ($this->dumper) {
            try {
                return $this->dumper->getFormattedException($contentType, $exception, $request);
            } catch (\RuntimeException $formattingException) {
                // Fallback to simple output
            }
        }

        if (in_array($contentType, ['text/json', 'application/json', 'application/x-json'], true)) {
            return $this->getJsonError($exception);
        }

        if (in_array($contentType, ['text/xml', 'application/xml', 'application/x-xml'], true)) {
            return $this->getXmlError($exception);
        }

        if (in_array($contentType, ['text/html', 'application/xhtml+xml'], true)) {
            return $this->getHtmlError($exception);
        }

        // Fallback to text/plain
        return $this->getTextError($exception);
    }

    /**
     * Get simple JSON formatted error.
     *
     * @param HttpException $exception
     *
     * @return string
     */
    protected function getJsonError(HttpException $exception): string
    {
        return sprintf(
            '{"error":{"ref":"%s","message":"Application error"}}',
            $exception->getIdentifier()
        );
    }

    /**
     * Get simple XML formatted error.
     *
     * @param HttpException $exception
     *
     * @return string
     */
    protected function getXmlError(HttpException $exception): string
    {
        return sprintf(
            '<?xml version="1.0" encoding="utf-8"?><root>' .
            '<error><ref>%s</ref><message>Application error</message></error>' .
            '</root>',
            $exception->getIdentifier()
        );
    }

    /**
     * Get simple HTML formatted error.
     *
     * @param HttpException $exception
     *
     * @return string
     */
    protected function getHtmlError(HttpException $exception): string
    {
        return sprintf(
            '<!DOCTYPE html><html lang="en"><head><meta http-equiv="Content-Type" content="text/html; ' .
            'charset=utf-8"><title>Application error</title><style>body{margin:0;padding:30px;font:12px/1.5 ' .
            'Helvetica,Arial,Verdana,sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;' .
            '}</style></head><body><h1>Application error (Ref. %s)</h1><p>An application error has occurred. ' .
            'Sorry for the temporary inconvenience.</p></body></html>',
            $exception->getIdentifier()
        );
    }

    /**
     * Get simple text formatted error.
     *
     * @param HttpException $exception
     *
     * @return string
     */
    protected function getTextError(HttpException $exception): string
    {
        return sprintf('(%s) Application error', $exception->getIdentifier());
    }

    /**
     * Log exception.
     *
     * @param RequestInterface $request
     * @param HttpException    $exception
     */
    protected function log(RequestInterface $request, HttpException $exception)
    {
        if (!$this->logger) {
            return;
        }

        $logContext = [
            'exception_id' => $exception->getIdentifier(),
            'http_method' => $request->getMethod(),
            'request_uri' => (string) $request->getUri(),
        ];
        $this->logger->log($this->getLogLevel($exception), $exception->getMessage(), $logContext);
    }

    /**
     * Check if running on CLI.
     *
     * @return bool
     */
    protected function isCli()
    {
        return PHP_SAPI === 'cli';
    }

    /**
     * Get log level.
     *
     * @param HttpException $exception
     *
     * @return string
     */
    final public function getLogLevel(HttpException $exception): string
    {
        while ($exception instanceof HttpException
            && $exception->getHttpStatusCode() === StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR
            && $exception->getPrevious() !== null) {
            $exception = $exception->getPrevious();
        }

        if ($exception instanceof \ErrorException && array_key_exists($exception->getSeverity(), $this->logLevelMap)) {
            return $this->logLevelMap[$exception->getSeverity()];
        }

        return LogLevel::ERROR;
    }

    /**
     * Determine which content type to use.
     *
     * @param RequestInterface $request
     *
     * @return string
     */
    protected function determineContentType(RequestInterface $request): string
    {
        $acceptHeader = $request->getHeaderLine('Accept');
        $selectedContentTypes = array_intersect(explode(',', $acceptHeader), $this->knownContentTypes);

        if (count($selectedContentTypes)) {
            return reset($selectedContentTypes);
        }

        return $this->defaultContentType;
    }

    /**
     * Get a new stream handler.
     *
     * @return StreamInterface
     */
    protected function getNewStream(): StreamInterface
    {
        return new Stream(fopen('php://temp', 'wb+'));
    }
}
