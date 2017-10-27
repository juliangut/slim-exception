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

use Psr\Http\Message\ResponseInterface;

/**
 * HTTP exception handling trait.
 */
trait HttpExceptionAwareTrait
{
    /**
     * Register custom error handlers.
     */
    protected function registerPhpErrorHandling()
    {
        set_error_handler([$this, 'errorHandler']);
        register_shutdown_function([$this, 'shutdownHandler']);
        set_exception_handler([$this, 'exceptionHandler']);

        ini_set('display_errors', 'off');

        error_reporting(-1);
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
    public function errorHandler(int $severity, string $message, string $file = null, int $line = null): bool
    {
        if (error_reporting() & $severity) {
            throw new \ErrorException($message, 0, $severity, $file, $line);
        }

        return false;
    }

    /**
     * Custom shutdown handler.
     *
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function shutdownHandler()
    {
        $error = $this->getLastError();

        if (count($error) && $this->isFatalError($error['type'])) {
            $this->exceptionHandler($this->getFatalException($error));

            // @codeCoverageIgnoreStart
            if (!defined('PHPUNIT_TEST')) {
                exit;
            }
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Custom exceptions handler.
     *
     * @param \Throwable $exception
     */
    public function exceptionHandler(\Throwable $exception)
    {
        $container = $this->getContainer();

        /* @var ResponseInterface $response */
        $response = call_user_func(
            $container->get('errorHandler'),
            $container->get('request'),
            $container->get('response'),
            $exception
        );

        $this->respond($response);
    }

    /**
     * Get last generated error.
     *
     * @return array
     */
    protected function getLastError(): array
    {
        return error_get_last() ?? [];
    }

    /**
     * Check if error is fatal.
     *
     * @param int $error
     *
     * @return bool
     */
    private function isFatalError(int $error): bool
    {
        $fatalErrors = E_ERROR
            | E_PARSE
            | E_CORE_ERROR
            | E_CORE_WARNING
            | E_COMPILE_ERROR
            | E_COMPILE_WARNING
            | E_USER_ERROR
            | E_STRICT;

        return ($error & $fatalErrors) !== 0;
    }

    /**
     * Get exception from fatal error.
     *
     * @param array $error
     *
     * @return HttpException
     */
    private function getFatalException(array $error): HttpException
    {
        $message = explode("\n", $error['message']);
        $message = preg_replace('/ in .+\.php(:\d+)?$/', '', $message[0]);

        $exception = HttpExceptionFactory::internalServerError($message, '', $error['type']);

        $trace = $this->getBackTrace();
        if (count($trace)) {
            $reflection = new \ReflectionProperty(\Exception::class, 'trace');
            $reflection->setAccessible(true);
            $reflection->setValue($exception, $trace);
        }

        return $exception;
    }

    /**
     * Get execution backtrace.
     *
     * @return array
     */
    private function getBackTrace(): array
    {
        $trace = [];

        if (function_exists('xdebug_get_function_stack')) {
            $trace = array_map(
                function (array $frame): array {
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
                xdebug_get_function_stack()
            );

            $trace = array_reverse(array_slice($trace, 0, -3));
        }

        return $trace;
    }

    /**
     * Enable access to the DI container by consumers of $app.
     *
     * @return \Psr\Container\ContainerInterface
     */
    abstract public function getContainer();

    /**
     * Send the response the client.
     *
     * @param ResponseInterface $response
     */
    abstract public function respond(ResponseInterface $response);
}
