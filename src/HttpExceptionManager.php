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

namespace Jgut\Slim\Exception;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;
use Slim\Http\Stream;

/**
 * HTTP exceptions manager.
 */
class HttpExceptionManager implements LoggerAwareInterface
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
     * List of HTTP status code handlers.
     *
     * @var HttpExceptionHandler[]
     */
    protected $handlers = [];

    /**
     * Default HTTP status code handler.
     *
     * @var HttpExceptionHandler
     */
    protected $defaultHandler;

    /**
     * HttpExceptionManager constructor.
     *
     * @param HttpExceptionHandler $defaultHandler
     */
    public function __construct(HttpExceptionHandler $defaultHandler)
    {
        $this->setDefaultHandler($defaultHandler);
    }

    /**
     * Set default HTTP status code handler.
     *
     * @param HttpExceptionHandler $defaultHandler
     */
    public function setDefaultHandler(HttpExceptionHandler $defaultHandler)
    {
        $this->defaultHandler = $defaultHandler;
    }

    /**
     * Add HTTP status code handler.
     *
     * @param int|array            $statusCodes
     * @param HttpExceptionHandler $handler
     */
    public function addHandler($statusCodes, HttpExceptionHandler $handler)
    {
        if (!is_array($statusCodes)) {
            $statusCodes = [$statusCodes];
        }

        $statusCodes = array_filter(
            $statusCodes,
            function ($statusCode) {
                return is_int($statusCode);
            }
        );

        foreach ($statusCodes as $statusCode) {
            $this->handlers[$statusCode] = $handler;
        }
    }

    /**
     * Get generic error handler.
     *
     * @return \Closure
     */
    public function getErrorHandler(): \Closure
    {
        return function (
            ServerRequestInterface $request,
            ResponseInterface $response,
            \Throwable $exception
        ): ResponseInterface {
            if (!$exception instanceof HttpException) {
                $exception = HttpExceptionFactory::internalServerError(null, null, $exception);
            }

            return $this->handleHttpException($request, $response, $exception);
        };
    }

    /**
     * Get 404 not found error handler.
     *
     * @return \Closure
     */
    public function getNotFoundHandler(): \Closure
    {
        return function (
            ServerRequestInterface $request,
            ResponseInterface $response
        ): ResponseInterface {
            return $this->handleHttpException($request, $response, HttpExceptionFactory::notFound());
        };
    }

    /**
     * Get 405 not allowed error handler.
     *
     * @return \Closure
     */
    public function getNotAllowedHandler(): \Closure
    {
        return function (
            ServerRequestInterface $request,
            ResponseInterface $response,
            array $methods = []
        ): ResponseInterface {
            if ($request->getMethod() === 'OPTIONS') {
                $body = new Stream(fopen('php://temp', 'wb+'));
                $body->write(
                    sprintf(
                        'Allowed method%s: %s',
                        count($methods) > 1 ? 's' : '',
                        implode(', ', $methods)
                    )
                );

                return $response
                    ->withStatus(StatusCodeInterface::STATUS_OK)
                    ->withHeader('Content-Type', 'text/plain; charset=utf-8')
                    ->withBody($body);
            }

            $exception = HttpExceptionFactory::methodNotAllowed(
                sprintf(
                    'Method "%s" not allowed. Must be%s %s',
                    $request->getMethod(),
                    count($methods) > 1 ? ' one of' : '',
                    implode(', ', $methods)
                )
            );

            return $this->handleHttpException($request, $response, $exception);
        };
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param HttpException          $exception
     *
     * @return ResponseInterface
     */
    public function handleHttpException(
        ServerRequestInterface $request,
        ResponseInterface $response,
        HttpException $exception
    ): ResponseInterface {
        $this->log($request, $exception);

        if ($this->isCli()) {
            $request = $request->withHeader('Accept', 'text/plain');
        }

        $handler = $this->defaultHandler;

        $statusCode = $exception->getHttpStatusCode();
        if (array_key_exists($statusCode, $this->handlers)) {
            $handler = $this->handlers[$statusCode];
        }

        return $handler->handleException($request, $response, $exception);
    }

    /**
     * Check if running on CLI.
     *
     * @return bool
     */
    protected function isCli(): bool
    {
        return PHP_SAPI === 'cli';
    }

    /**
     * Log exception.
     *
     * @param ServerRequestInterface $request
     * @param HttpException          $exception
     */
    protected function log(ServerRequestInterface $request, HttpException $exception)
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
}
