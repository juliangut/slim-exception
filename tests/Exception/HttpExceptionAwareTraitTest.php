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

namespace Jgut\Slim\Exception\Tests;

use Jgut\Slim\Exception\Tests\Stubs\AppStub;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Slim\Http\Response;

/**
 * HTTP exception handling trait tests.
 */
class HttpExceptionAwareTraitTest extends TestCase
{
    /**
     * @var bool
     */
    protected static $exitShutDown = false;

    /**
     * {@inheritdoc}
     *
     * Hack to prevent shutdown function to be triggered after PHPUnit has finished.
     */
    public static function setUpBeforeClass()
    {
        \register_shutdown_function([__CLASS__, 'shutDown']);
    }

    public function testIgnoredError()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        /* @var ContainerInterface $container */

        $app = new AppStub($container);

        \error_reporting(E_USER_ERROR);

        $app->errorHandler(E_USER_NOTICE, 'Custom notice', __FILE__, __LINE__);

        \ob_start();

        $app->shutdownHandler();

        self::assertEquals('', \ob_get_clean());
    }

    public function testHandleExceptionFromError()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $container->expects(self::at(0))
            ->method('get')
            ->with('errorHandler')
            ->will(self::returnValue(function () {
                $response = new Response();
                $response->getBody()->write('Internal server error');

                return $response;
            }));
        /* @var ContainerInterface $container */

        $app = new AppStub($container);

        \error_reporting(E_ALL);

        try {
            $app->errorHandler(E_PARSE, 'Parse error', __FILE__, __LINE__);
        } catch (\Exception $exception) {
            \ob_start();

            $app->exceptionHandler($exception);

            self::assertContains('Internal server error', \ob_get_clean());
        }
    }

    public function testFatalError()
    {
        $error = [
            'type' => E_USER_ERROR,
            'message' => 'User error in /path/to/file.php',
            'file' => __FILE__,
            'line' => __LINE__,
        ];
        $errorHandler = function () {
            $response = new Response();
            $response->getBody()->write('Internal server error');

            return $response;
        };

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $container->expects(self::any())
            ->method('get')
            ->will(self::returnValue($errorHandler));
        /* @var ContainerInterface $container */

        $app = new AppStub($container, $error);

        \ob_start();

        $app->shutdownHandler();

        self::assertContains('Internal server error', \ob_get_clean());

        static::$exitShutDown = true;
    }

    /**
     * Hack to prevent shutdown function to be triggered after PHPUnit has finished.
     */
    public static function shutDown()
    {
        if (static::$exitShutDown) {
            exit;
        }
    }
}
