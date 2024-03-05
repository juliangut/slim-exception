<?php

/*
 * (c) 2017-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-exception
 */

declare(strict_types=1);

namespace Jgut\Slim\Exception\Tests;

use Jgut\Slim\Exception\Tests\Stubs\ExceptionHandlerStub;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;
use Slim\Interfaces\ErrorHandlerInterface;
use Throwable;

/**
 * @internal
 */
class ExceptionHandlerTest extends TestCase
{
    protected static bool $exitShutDown = false;

    /**
     * Hack to prevent shutdown function to be triggered after PHPUnit has finished.
     */
    public static function setUpBeforeClass(): void
    {
        register_shutdown_function([__CLASS__, 'shutDown']);
    }

    public function testIgnoredError(): void
    {
        $errorHandler = $this->getMockBuilder(ErrorHandlerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $exceptionHandler = new ExceptionHandlerStub(new ServerRequest(), $errorHandler, false, false, false);
        $exceptionHandler->registerHandling();

        error_reporting(\E_USER_ERROR);

        $exceptionHandler->handleError(\E_USER_NOTICE, 'Custom notice', __FILE__, __LINE__);

        ob_start();

        $exceptionHandler->handleShutdown();

        static::assertEquals('', ob_get_clean());
    }

    public function testHandleExceptionFromError(): void
    {
        $response = (new ResponseFactory())->createResponse();
        $response->getBody()
            ->write('Exception!');

        $errorHandler = $this->getMockBuilder(ErrorHandlerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $errorHandler->expects(static::once())
            ->method('__invoke')
            ->willReturn($response);

        $exceptionHandler = new ExceptionHandlerStub(new ServerRequest(), $errorHandler, false, false, false);
        $exceptionHandler->registerHandling();

        error_reporting(\E_ALL);

        try {
            $exceptionHandler->handleError(\E_PARSE, 'Parse error', __FILE__, __LINE__);
        } catch (Throwable $exception) {
            ob_start();

            $exceptionHandler->handleException($exception);

            static::assertEquals('Exception!', ob_get_clean());
        }
    }

    public function testFatalError(): void
    {
        $error = [
            'type' => \E_USER_ERROR,
            'message' => 'User error in /path/to/file.php',
            'file' => __FILE__,
            'line' => __LINE__,
        ];
        $response = (new ResponseFactory())->createResponse();
        $response->getBody()
            ->write('Exception!');

        $errorHandler = $this->getMockBuilder(ErrorHandlerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $errorHandler->expects(static::once())
            ->method('__invoke')
            ->willReturn($response);

        $exceptionHandler = new ExceptionHandlerStub(new ServerRequest(), $errorHandler, false, false, false, $error);
        $exceptionHandler->registerHandling();

        ob_start();

        $exceptionHandler->handleShutdown();

        static::assertEquals('Exception!', ob_get_clean());

        static::$exitShutDown = true;
    }

    /**
     * Hack to prevent shutdown function to be triggered after PHPUnit has finished.
     *
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public static function shutDown(): void
    {
        if (static::$exitShutDown) {
            exit;
        }
    }
}
