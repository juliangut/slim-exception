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

namespace Jgut\Slim\Exception\Tests\Whoops\Formatter;

use Jgut\Slim\Exception\HttpExceptionFactory;
use Jgut\Slim\Exception\Whoops\Formatter\Text;
use PHPUnit\Framework\TestCase;
use Whoops\Exception\Inspector;

/**
 * Whoops custom plain text HTTP exception formatter tests.
 */
class TextTest extends TestCase
{
    /**
     * @var Text
     */
    protected $formatter;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->formatter = new Text();
    }

    public function testContentType()
    {
        $contentTypes = [
            'text/plain',
        ];

        self::assertEquals($contentTypes, $this->formatter->getContentTypes());
    }

    public function testNoTraceOutput()
    {
        $exception = HttpExceptionFactory::tooManyRequests();
        $inspector = new Inspector($exception);

        $this->formatter->addTraceToOutput(false);
        $this->formatter->setException($exception);
        $this->formatter->setInspector($inspector);

        ob_start();
        $this->formatter->handle();
        $output = ob_get_clean();

        self::assertNotRegExp('/Stack trace:/', $output);
    }

    public function testOutput()
    {
        $originalException = new \ErrorException('Original exception');
        $exception = HttpExceptionFactory::tooManyRequests('', '', null, $originalException);
        $inspector = new Inspector($exception);

        $handler = new Text();
        $handler->addTraceFunctionArgsToOutput(true);
        $handler->setException($exception);
        $handler->setInspector($inspector);

        ob_start();
        $handler->handle();
        $output = ob_get_clean();

        self::assertRegExp('/^\(.+\) Jgut\\\\Slim\\\\Exception\\\\HttpException/', $output);
        self::assertRegExp('/Stack trace:/', $output);
    }
}
