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

use Fig\Http\Message\StatusCodeInterface;
use Jgut\HttpException\InternalServerErrorHttpException;
use Jgut\Slim\Exception\Whoops\Formatter\Html;
use PHPUnit\Framework\TestCase;
use Whoops\Exception\Inspector;
use Whoops\Run as Whoops;

/**
 * Whoops custom HTML HTTP exception formatter tests.
 */
class HtmlTest extends TestCase
{
    /**
     * @var Html
     */
    protected $formatter;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->formatter = new Html();
    }

    public function testContentType()
    {
        $contentTypes = [
            'text/html',
            'application/xhtml+xml',
        ];

        self::assertEquals($contentTypes, $this->formatter->getContentTypes());
    }

    public function testOutput()
    {
        $exception = new InternalServerErrorHttpException(
            'Impossible error',
            '',
            StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR,
            new \ErrorException()
        );
        $inspector = new Inspector($exception);
        $whoops = new Whoops();

        $this->formatter->handleUnconditionally(true);
        $this->formatter->setException($exception);
        $this->formatter->setInspector($inspector);
        $this->formatter->setRun($whoops);
        $this->formatter->setApplicationPaths([
            \dirname(__DIR__, 4) . '/src/Exception/HttpExceptionFactory.php',
            __FILE__,
        ]);

        \ob_start();
        $this->formatter->handle();
        $output = \ob_get_clean();

        self::assertContains(InternalServerErrorHttpException::class, $output);
        self::assertContains('Impossible error', $output);
    }
}
