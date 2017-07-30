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
    protected function registerErrorHandling()
    {
        set_error_handler([$this, 'errorHandler']);
        register_shutdown_function([$this, 'shutdownHandler']);
        set_exception_handler([$this, 'exceptionHandler']);
    }

    /**
     * Custom errors handler.
     * Transforms unhandled errors into exceptions.
     *
     * @param int    $error
     * @param string $message
     * @param string $file
     * @param int    $line
     *
     * @throws \ErrorException
     *
     * @return bool
     */
    public function errorHandler(int $error, string $message, string $file = null, int $line = null): bool
    {
        if ($error & error_reporting()) {
            throw new \ErrorException($message, 0, $error, $file, $line);
        }

        return false;
    }

    /**
     * Custom shutdown handler.
     */
    public function shutdownHandler()
    {
        $error = $this->getLastError();

        if (count($error) && $this->isFatalError($error['type'])) {
            $message = explode("\n", $error['message']);
            $message = preg_replace('/ in .+\.php:\d+$/', '', $message[0]);

            $this->exceptionHandler(new \ErrorException($message, 0, $error['type'], $error['file'], $error['line']));
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

        $handler = $container->get('errorHandler');
        $request = $container->get('request');
        $response = $container->get('response');

        /* @var ResponseInterface $response */
        $response = call_user_func($handler, $request, $response, $exception);

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
