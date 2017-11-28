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
use Jgut\Slim\Exception\Whoops\Formatter\Xml;
use PHPUnit\Framework\TestCase;
use Whoops\Exception\Inspector;

/**
 * Whoops custom XML HTTP exception formatter tests.
 */
class XmlTest extends TestCase
{
    /**
     * @var Xml
     */
    protected $formatter;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->formatter = new Xml();
    }

    public function testContentType()
    {
        $contentTypes = [
            'application/xml',
            'text/xml',
            'application/x-xml',
            'application/*+xml',
        ];

        self::assertEquals($contentTypes, $this->formatter->getContentTypes());
    }

    public function testXmlOutput()
    {
        $exception = HttpExceptionFactory::forbidden('Forbidden');
        $inspector = new Inspector($exception);

        $this->formatter->addTraceToOutput(true);
        $this->formatter->setException($exception);
        $this->formatter->setInspector($inspector);

        ob_start();
        $this->formatter->handle();
        $output = ob_get_clean();

        self::assertRegExp('!<id>.+</id>!', $output);
        self::assertRegExp('!<message>Forbidden</message>!', $output);
    }
}
