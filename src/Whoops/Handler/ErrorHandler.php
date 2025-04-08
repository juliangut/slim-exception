<?php

/*
 * (c) 2017-2025 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-exception
 */

declare(strict_types=1);

namespace Jgut\Slim\Exception\Whoops\Handler;

use InvalidArgumentException;
use Jgut\Slim\Exception\Handler\ErrorHandler as BaseErrorHandler;
use Jgut\Slim\Exception\Whoops\Renderer\HtmlRenderer;
use Jgut\Slim\Exception\Whoops\Renderer\JsonRenderer;
use Jgut\Slim\Exception\Whoops\Renderer\PlainTextRenderer;
use Jgut\Slim\Exception\Whoops\Renderer\XmlRenderer;
use Negotiation\Negotiator;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Slim\App;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\ErrorRendererInterface as ErrorRenderer;
use Throwable;
use Whoops\Exception\Frame;
use Whoops\Handler\HandlerInterface;
use Whoops\Run as Whoops;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ErrorHandler extends BaseErrorHandler
{
    protected const REQUEST_DATA_TABLE_LABEL = 'Slim Application (Request)';

    /**
     * @var ErrorRenderer|class-string<ErrorRenderer>|callable(Throwable, bool): string
     */
    protected $logErrorRenderer = PlainTextRenderer::class;

    /**
     * @var ErrorRenderer|class-string<ErrorRenderer>|callable(Throwable, bool): string
     */
    protected $defaultErrorRenderer = HtmlRenderer::class;

    /**
     * @var array<string, ErrorRenderer|class-string<ErrorRenderer>|callable(Throwable, bool): string>
     */
    protected array $errorRenderers = [
        'text/html' => HtmlRenderer::class,
        'application/xhtml+xml' => HtmlRenderer::class,
        'application/json' => JsonRenderer::class,
        'text/json' => JsonRenderer::class,
        'application/x-json' => JsonRenderer::class,
        'application/*+json' => JsonRenderer::class,
        'application/xml' => XmlRenderer::class,
        'text/xml' => XmlRenderer::class,
        'application/x-xml' => XmlRenderer::class,
        'application/*+xml' => XmlRenderer::class,
        'text/plain' => PlainTextRenderer::class,
    ];

    protected Whoops $whoops;

    public function __construct(
        CallableResolverInterface $callableResolver,
        ResponseFactoryInterface $responseFactory,
        Negotiator $negotiator,
        Whoops $whoops,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($callableResolver, $responseFactory, $negotiator, $logger);

        $clonedWhoops = clone $whoops;
        $clonedWhoops->clearHandlers();
        $clonedWhoops->allowQuit(false);
        $clonedWhoops->writeToOutput(false);

        $excludedPathRegex = \sprintf('!^%s/.+\.php$!', \dirname(__DIR__, 3));
        $clonedWhoops->addFrameFilter(
            static fn(Frame $frame): bool => preg_match($excludedPathRegex, $frame->getFile() ?? '') !== 1,
        );

        $this->whoops = $clonedWhoops;
    }

    /**
     * @throws InvalidArgumentException
     * @throws RuntimeException
     *
     * @return callable(Throwable): string
     */
    protected function determineRenderer(): callable
    {
        return $this->getRenderer(parent::determineRenderer());
    }

    /**
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    protected function determineLogRenderer(): callable
    {
        $renderer = parent::determineLogRenderer();

        return $this->getRenderer($renderer);
    }

    /**
     * @param HandlerInterface|mixed $renderer
     *
     * @throws InvalidArgumentException
     *
     * @return callable(Throwable): string
     */
    protected function getRenderer(mixed $renderer): callable
    {
        if (!$renderer instanceof HandlerInterface) {
            throw new InvalidArgumentException(\sprintf(
                'Renderer "%s" for Whoops error handler should implement "%s".',
                \is_object($renderer) ? $renderer::class : \gettype($renderer),
                HandlerInterface::class,
            ));
        }

        if ($renderer instanceof HtmlRenderer || is_subclass_of($renderer, HtmlRenderer::class)) {
            $renderer = $this->addRequestData($renderer);
        }

        return function (Throwable $exception) use ($renderer): string {
            if ($renderer instanceof HtmlRenderer) {
                $renderer->handleUnconditionally(true);
            }

            $this->whoops->appendHandler($renderer);

            $output = $this->whoops->handleException($exception);

            $this->whoops->clearHandlers();

            return $output;
        };
    }

    protected function addRequestData(HtmlRenderer $renderer): HtmlRenderer
    {
        $extra = $renderer->getDataTables(self::REQUEST_DATA_TABLE_LABEL);
        if (\is_array($extra) && \count($extra) === 0) {
            $acceptHeader = $this->request->getHeader('Accept');
            $contentTypeHeader = $this->request->getHeader('Content-Type');
            $queryString = $this->request->getUri()
                ->getQuery();

            $renderer->addDataTable(
                self::REQUEST_DATA_TABLE_LABEL,
                [
                    'Accept Charset' => \count($acceptHeader) !== 0 ? $acceptHeader : '<none>',
                    'Content Charset' => \count($contentTypeHeader) !== 0 ? $contentTypeHeader : '<none>',
                    'HTTP Method' => $this->request->getMethod(),
                    'Path' => $this->request->getUri()
                        ->getPath(),
                    'Query String' => $queryString !== '' ? $queryString : '<none>',
                    'Base URL' => (string) $this->request->getUri(),
                    'Scheme' => $this->request->getUri()
                        ->getScheme(),
                    'Port' => $this->request->getUri()
                        ->getPort(),
                    'Host' => $this->request->getUri()
                        ->getHost(),
                    'Slim Version' => App::VERSION,
                ],
            );
        }

        return $renderer;
    }
}
