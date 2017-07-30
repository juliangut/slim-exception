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

namespace Jgut\Slim\Tests\Exception\Dumper\Whoops;

use Jgut\Slim\Exception\Dumper\Whoops\ExceptionDumper;
use Jgut\Slim\Exception\HttpExceptionFactory;
use PHPUnit\Framework\TestCase;
use Slim\Http\Environment;
use Slim\Http\Request;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\XmlResponseHandler;
use Whoops\Run as Whoops;

/**
 * Whoops HTTP exception dumper tests.
 */
class ExceptionDumperTest extends TestCase
{
    /**
     * @var Whoops
     */
    protected $whoops;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->whoops = new Whoops();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /No content defined for .+ handler/
     */
    public function testInvalidHandler()
    {
        $handler = $this->getMockBuilder(PlainTextHandler::class)
            ->getMock();
        $handler->expects(self::once())
            ->method('contentType')
            ->will($this->returnValue(null));
        /* @var PlainTextHandler $handler */

        $whoops = new Whoops();
        $whoops->pushHandler($handler);

        new ExceptionDumper($whoops);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /There is no defined handler for ".+"/
     */
    public function testInvalidContentType()
    {
        $dumper = new ExceptionDumper($this->whoops);

        $exception = HttpExceptionFactory::badRequest();
        $request = Request::createFromEnvironment(Environment::mock());

        $dumper->getFormattedException('text/unknown', $exception, $request);
    }

    public function testPlainText()
    {
        $handler = $this->getMockBuilder(PlainTextHandler::class)
            ->setMethodsExcept(['contentType'])
            ->getMock();
        $handler->expects(self::once())->method('handle');
        /* @var PlainTextHandler $handler */

        $dumper = new ExceptionDumper($this->whoops);
        $dumper->addHandler($handler);

        $exception = HttpExceptionFactory::badRequest();
        $request = Request::createFromEnvironment(Environment::mock());

        $dumper->getFormattedException('text/plain', $exception, $request);
    }

    public function testJson()
    {
        $handler = $this->getMockBuilder(JsonResponseHandler::class)
            ->setMethodsExcept(['contentType'])
            ->getMock();
        $handler->expects(self::once())->method('handle');
        /* @var JsonResponseHandler $handler */

        $dumper = new ExceptionDumper($this->whoops);
        $dumper->addHandler($handler);

        $exception = HttpExceptionFactory::forbidden();
        $request = Request::createFromEnvironment(Environment::mock());

        $dumper->getFormattedException('application/json', $exception, $request);
    }

    public function testXml()
    {
        $handler = $this->getMockBuilder(XmlResponseHandler::class)
            ->setMethodsExcept(['contentType'])
            ->getMock();
        $handler->expects(self::once())->method('handle');
        /* @var XmlResponseHandler $handler */

        $dumper = new ExceptionDumper($this->whoops);
        $dumper->addHandler($handler);

        $exception = HttpExceptionFactory::conflict();
        $request = Request::createFromEnvironment(Environment::mock());

        $dumper->getFormattedException('application/xml', $exception, $request);
    }

    public function testHtml()
    {
        $handler = $this->getMockBuilder(PrettyPageHandler::class)
            ->setMethodsExcept(['contentType'])
            ->getMock();
        $handler->expects(self::once())->method('handle');
        /* @var PrettyPageHandler $handler */

        $dumper = new ExceptionDumper($this->whoops);
        $dumper->addHandler($handler);

        $exception = HttpExceptionFactory::tooManyRequests();
        $request = Request::createFromEnvironment(Environment::mock());

        $dumper->getFormattedException('text/html', $exception, $request);
    }
}
