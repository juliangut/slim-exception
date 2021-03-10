<?php

/*
 * slim-exception (https://github.com/juliangut/slim-exception).
 * Slim exception handling.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-exception
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

declare(strict_types=1);

namespace Jgut\Slim\Exception\Tests\Renderer;

use Jgut\Slim\Exception\Renderer\JsonRenderer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpForbiddenException;

/**
 * JSON exception renderer tests.
 */
class JsonRendererTest extends TestCase
{
    /**
     * @var HttpForbiddenException
     */
    protected $exception;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $this->exception = new HttpForbiddenException($request, 'Forbidden action');
    }

    public function testOutput(): void
    {
        $output = (new JsonRenderer())($this->exception, false);

        $expected = <<<'EXPECTED'
{
    "error": {
        "message": "403 Forbidden"
    }
}
EXPECTED;
        static::assertEquals($expected, $output);
    }

    public function testNotPrettifiedOutput(): void
    {
        $renderer = new JsonRenderer();
        $renderer->setPrettify(false);

        $output = $renderer($this->exception, false);

        static::assertEquals('{"error":{"message":"403 Forbidden"}}', $output);
    }

    public function testOutputWithTrace(): void
    {
        $output = (new JsonRenderer())($this->exception, true);

        $expected = <<<'EXPECTED'
{
    "error": {
        "message": "403 Forbidden",
        "exception": [

EXPECTED;
        static::assertStringContainsString($expected, $output);
    }

    public function testNotPrettifiedOutputWithTrace(): void
    {
        $renderer = new JsonRenderer();
        $renderer->setPrettify(false);

        $output = $renderer($this->exception, true);

        static::assertStringContainsString('{"error":{"message":"403 Forbidden","exception":[', $output);
    }
}
