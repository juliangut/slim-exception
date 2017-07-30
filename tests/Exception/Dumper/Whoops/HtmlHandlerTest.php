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

namespace Jgut\Slim\Exception\Tests\Dumper\Whoops;

use Jgut\Slim\Exception\Dumper\Whoops\HtmlHandler;
use Jgut\Slim\Exception\HttpExceptionFactory;
use PHPUnit\Framework\TestCase;
use Whoops\Exception\Inspector;
use Whoops\Run as Whoops;

/**
 * Whoops custom HTML response handler tests.
 */
class HtmlHandlerTest extends TestCase
{
    public function testHtmlOutput()
    {
        $exception = HttpExceptionFactory::internalServerError('Impossible error', null, new \ErrorException());
        $inspector = new Inspector($exception);
        $whoops = new Whoops();

        $handler = new HtmlHandler();
        $handler->setException($exception);
        $handler->setInspector($inspector);
        $handler->setRun($whoops);
        $handler->setApplicationPaths([
            dirname(dirname(dirname(dirname(__DIR__)))) . '/src/Exception/HttpExceptionFactory.php',
        ]);
        $handler->addCustomCss('js/zepto.min.js');
        $handler->addDataTable('A', []);

        ob_start();
        $handler->handle();
        $output = ob_get_clean();

        self::assertRegExp('/\(.+\) Jgut\\\\Slim\\\\Exception\\\\HttpException/', $output);
    }
}
