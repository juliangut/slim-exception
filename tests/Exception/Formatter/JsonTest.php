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

namespace Jgut\Slim\Exception\Tests\Formatter;

use Jgut\HttpException\ForbiddenHttpException;
use Jgut\Slim\Exception\Formatter\Json;
use PHPUnit\Framework\TestCase;
use Slim\Http\Request;

/**
 * JSON HTTP exception formatter tests.
 */
class JsonTest extends TestCase
{
    /**
     * @var Json
     */
    protected $formatter;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->formatter = new Json();
    }

    public function testContentType()
    {
        $contentTypes = [
            'application/json',
            'text/json',
            'application/x-json',
            'application/*+json',
        ];

        self::assertEquals($contentTypes, $this->formatter->getContentTypes());
    }

    public function testOutput()
    {
        /* @var \Psr\Http\Message\ServerRequestInterface $request */
        $request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $exception = new ForbiddenHttpException('Forbidden');

        $output = $this->formatter->formatException($exception, $request);

        self::assertRegExp('/"id":".+"/', $output);
        self::assertRegExp('/"message":"Forbidden"/', $output);
    }
}
