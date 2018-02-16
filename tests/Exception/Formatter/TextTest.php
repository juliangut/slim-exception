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
use Jgut\Slim\Exception\Formatter\Text;
use PHPUnit\Framework\TestCase;
use Slim\Http\Request;

/**
 * Plain text HTTP exception formatter tests.
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

    public function testOutput()
    {
        /* @var \Psr\Http\Message\ServerRequestInterface $request */
        $request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $exception = new ForbiddenHttpException('Forbidden');

        $output = $this->formatter->formatException($exception, $request);

        self::assertRegExp('/^\(.+\) Forbidden/', $output);
    }
}
