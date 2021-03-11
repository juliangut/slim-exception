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

namespace Jgut\Slim\Exception;

use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpException;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Interfaces\ErrorHandlerInterface;
use Slim\ResponseEmitter;

/**
 * Exception handling.
 */
class ExceptionHandler
{
    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var ErrorHandlerInterface
     */
    protected $errorHandler;

    /**
     * @var bool
     */
    protected $displayErrorDetails;

    /**
     * @var bool
     */
    protected $logErrors;

    /**
     * @var bool
     */
    protected $logErrorDetails;

    /**
     * ExceptionHandler constructor.
     *
     * @param ServerRequestInterface $request
     * @param ErrorHandlerInterface  $errorHandler
     * @param bool                   $displayErrorDetails
     * @param bool                   $logErrors
     * @param bool                   $logErrorDetails
     */
    public function __construct(
        ServerRequestInterface $request,
        ErrorHandlerInterface $errorHandler,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ) {
        $this->request = $request;
        $this->errorHandler = $errorHandler;
        $this->displayErrorDetails = $displayErrorDetails;
        $this->logErrors = $logErrors;
        $this->logErrorDetails = $logErrorDetails;
    }

    /**
     * Register exception handling.
     */
    public function registerHandling(): void
    {
        \set_exception_handler([$this, 'handleException']);
        \set_error_handler([$this, 'handleError']);
        \register_shutdown_function([$this, 'handleShutdown']);

        \ini_set('display_errors', 'off');
    }

    /**
     * Custom exceptions handler.
     *
     * @param \Throwable $exception
     */
    public function handleException(\Throwable $exception): void
    {
        $response = \call_user_func(
            $this->errorHandler,
            $exception instanceof HttpException ? $exception->getRequest() : $this->request,
            $exception,
            $this->displayErrorDetails,
            $this->logErrors,
            $this->logErrorDetails
        );

        (new ResponseEmitter())->emit($response);
    }

    /**
     * Custom errors handler.
     * Transforms unhandled errors into exceptions.
     *
     * @param int         $severity
     * @param string      $message
     * @param string|null $file
     * @param int|null    $line
     *
     * @throws \ErrorException
     *
     * @return bool
     */
    public function handleError(int $severity, string $message, ?string $file = null, ?int $line = null): bool
    {
        if ((\error_reporting() & $severity) !== 0) {
            /**
             * @var string $file
             * @var int    $line
             */
            throw new \ErrorException(\rtrim($message, '.') . '.', $severity, $severity, $file, $line);
        }

        return false;
    }

    /**
     * Custom shutdown handler.
     *
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function handleShutdown(): void
    {
        $error = $this->getLastError();
        if (\count($error) !== 0 && $this->isFatalError($error['type'])) {
            $this->handleException($this->getFatalException($error));

            // @codeCoverageIgnoreStart
            if (!\defined('PHPUNIT_TEST')) {
                exit;
            }
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Get last generated error.
     *
     * @return mixed[]
     */
    protected function getLastError(): array
    {
        return \error_get_last() ?? [];
    }

    /**
     * Check if error is fatal.
     *
     * @param int $error
     *
     * @return bool
     */
    protected function isFatalError(int $error): bool
    {
        $fatalErrors = \E_ERROR
            | \E_PARSE
            | \E_CORE_ERROR
            | \E_CORE_WARNING
            | \E_COMPILE_ERROR
            | \E_COMPILE_WARNING
            | \E_USER_ERROR
            | \E_STRICT;

        return ($error & $fatalErrors) !== 0;
    }

    /**
     * Get exception from fatal error.
     *
     * @param mixed[] $error
     *
     * @return HttpException
     */
    private function getFatalException(array $error): HttpException
    {
        $message = \explode("\n", $error['message']);
        $message = $error['type'] . ' - ' . \preg_replace('/ in .+\.php(:\d+)?$/', '', $message[0]);

        $exception = new HttpInternalServerErrorException($this->request, $message);

        $trace = $this->getBackTrace();
        if (\count($trace) !== 0) {
            $reflection = new \ReflectionProperty(\Exception::class, 'trace');
            $reflection->setAccessible(true);
            $reflection->setValue($exception, $trace);
        }

        return $exception;
    }

    /**
     * Get execution backtrace.
     *
     * @return mixed[]
     */
    private function getBackTrace(): array
    {
        $trace = [];

        if (\function_exists('xdebug_get_function_stack')) {
            $trace = \array_map(
                static function (array $frame): array {
                    if (!isset($frame['type'])) {
                        // http://bugs.xdebug.org/view.php?id=695
                        if (isset($frame['class'])) {
                            $frame['type'] = '::';
                        }
                    } elseif ('static' === $frame['type']) {
                        $frame['type'] = '::';
                    } elseif ('dynamic' === $frame['type']) {
                        $frame['type'] = '->';
                    }

                    if (isset($frame['params'])) {
                        if (!isset($frame['args'])) {
                            $frame['args'] = $frame['params'];
                        }

                        unset($frame['params']);
                    }

                    return $frame;
                },
                \xdebug_get_function_stack()
            );

            $trace = \array_reverse(\array_slice($trace, 0, -3));
        }

        return $trace;
    }
}
