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

use ErrorException;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionProperty;
use Slim\Exception\HttpException;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Interfaces\ErrorHandlerInterface;
use Slim\ResponseEmitter;
use Throwable;

class ExceptionHandler
{
    protected ServerRequestInterface $request;

    protected ErrorHandlerInterface $errorHandler;

    protected bool $displayErrorDetails;

    protected bool $logErrors;

    protected bool $logErrorDetails;

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
        set_exception_handler([$this, 'handleException']);
        set_error_handler([$this, 'handleError']);
        register_shutdown_function([$this, 'handleShutdown']);

        ini_set('display_errors', 'off');
    }

    public function handleException(Throwable $exception): void
    {
        /** @var ResponseInterface $response */
        $response = \call_user_func(
            $this->errorHandler,
            $exception instanceof HttpException ? $exception->getRequest() : $this->request,
            $exception,
            $this->displayErrorDetails,
            $this->logErrors,
            $this->logErrorDetails,
        );

        (new ResponseEmitter())->emit($response);
    }

    /**
     * Custom errors handler.
     *
     * Transforms unhandled errors into exceptions.
     *
     * @throws ErrorException
     */
    public function handleError(int $severity, string $message, ?string $file = null, ?int $line = null): bool
    {
        if ((error_reporting() & $severity) !== 0) {
            /**
             * @var string $file
             * @var int    $line
             */
            throw new ErrorException(rtrim($message, '.') . '.', $severity, $severity, $file, $line);
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
        if ($error !== null && $this->isFatalError($error['type'])) {
            $this->handleException($this->getFatalException($error));

            // @codeCoverageIgnoreStart
            if (!\defined('PHPUNIT_TEST')) {
                exit;
            }
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * @return array{type: int, message: string, file: string, line: int}|null
     */
    protected function getLastError(): ?array
    {
        return error_get_last();
    }

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
     * @param array{type: int, message: string, file: string, line: int} $error
     */
    private function getFatalException(array $error): HttpException
    {
        $message = explode("\n", $error['message']);
        $message = $error['type'] . ' - ' . preg_replace('/ in .+\.php(:\d+)?$/', '', $message[0]);

        $exception = new HttpInternalServerErrorException($this->request, $message);

        $trace = $this->getBackTrace();
        if (\count($trace) !== 0) {
            $reflection = new ReflectionProperty(Exception::class, 'trace');
            $reflection->setAccessible(true);
            $reflection->setValue($exception, $trace);
        }

        return $exception;
    }

    /**
     * @return array<array<string, mixed>>
     */
    private function getBackTrace(): array
    {
        $trace = [];

        if (\function_exists('xdebug_get_function_stack')) {
            try {
                $trace = array_map(
                    fn(array $frame): array => $this->normalizeFrame($frame),
                    xdebug_get_function_stack(),
                );
            } catch (ErrorException $exception) {
                // @ignoreException
            }

            $trace = array_reverse(\array_slice($trace, 0, -3));
        }

        return $trace;
    }

    /**
     * @param array{type?: string, class?: string, params?: array<string, mixed>, args?: array<string, mixed>} $frame
     *
     * @return array{type?: string, class?: string, args?: array<string, mixed>}
     */
    private function normalizeFrame(array $frame): array
    {
        if (!\array_key_exists('type', $frame)) {
            // @see http://bugs.xdebug.org/view.php?id=695
            if (\array_key_exists('class', $frame)) {
                $frame['type'] = '::';
            }
        } elseif ($frame['type'] === 'static') {
            $frame['type'] = '::';
        } elseif ($frame['type'] === 'dynamic') {
            $frame['type'] = '->';
        }

        if (\array_key_exists('params', $frame)) {
            if (!\array_key_exists('args', $frame)) {
                $frame['args'] = $frame['params'];
            }

            unset($frame['params']);
        }

        return $frame;
    }
}
