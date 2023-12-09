<?php

/*
 * (c) 2017-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-exception
 */

declare(strict_types=1);

namespace Jgut\Slim\Exception\Handler;

use ErrorException;
use Jgut\Slim\Exception\Renderer\HtmlRenderer;
use Jgut\Slim\Exception\Renderer\JsonRenderer;
use Jgut\Slim\Exception\Renderer\PlainTextRenderer;
use Jgut\Slim\Exception\Renderer\XmlRenderer;
use Negotiation\BaseAccept;
use Negotiation\Exception\Exception as NegotiateException;
use Negotiation\Negotiator;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Slim\Handlers\ErrorHandler as SlimErrorHandler;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\ErrorRendererInterface as ErrorRenderer;
use Throwable;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ErrorHandler extends SlimErrorHandler
{
    /**
     * @var ErrorRenderer|class-string<ErrorRenderer>|callable(Throwable, bool): string
     */
    protected $logErrorRenderer = PlainTextRenderer::class;

    /**
     * @var array<int, string>
     */
    private array $errorToLogLevelMap = [
        \E_ERROR => LogLevel::ALERT,
        \E_WARNING => LogLevel::WARNING,
        \E_PARSE => LogLevel::ALERT,
        \E_NOTICE => LogLevel::NOTICE,
        \E_CORE_ERROR => LogLevel::ALERT,
        \E_CORE_WARNING => LogLevel::WARNING,
        \E_COMPILE_ERROR => LogLevel::ALERT,
        \E_COMPILE_WARNING => LogLevel::WARNING,
        \E_USER_ERROR => LogLevel::ERROR,
        \E_USER_WARNING => LogLevel::WARNING,
        \E_USER_NOTICE => LogLevel::NOTICE,
        \E_STRICT => LogLevel::WARNING,
        \E_RECOVERABLE_ERROR => LogLevel::ERROR,
        \E_DEPRECATED => LogLevel::WARNING,
        \E_USER_DEPRECATED => LogLevel::WARNING,
    ];

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

    public function __construct(
        CallableResolverInterface $callableResolver,
        ResponseFactoryInterface $responseFactory,
        protected Negotiator $negotiator,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($callableResolver, $responseFactory, $logger);
    }

    /**
     * @param array<string, ErrorRenderer|class-string<ErrorRenderer>|callable(Throwable, bool): string> $renderers
     */
    public function setErrorRenderers(array $renderers): void
    {
        $this->errorRenderers = [];

        foreach ($renderers as $contentType => $renderer) {
            $this->setErrorRenderer($contentType, $renderer);
        }
    }

    /**
     * @param ErrorRenderer|class-string<ErrorRenderer>|callable(Throwable, bool): string $errorRenderer
     */
    public function setErrorRenderer(string $contentType, ErrorRenderer|string|callable $errorRenderer): void
    {
        $this->errorRenderers[$contentType] = $errorRenderer;
    }

    protected function determineContentType(ServerRequestInterface $request): ?string
    {
        if ($this->inCli()) {
            return 'text/plain';
        }

        $header = trim($request->getHeaderLine('Accept'));
        $priorities = array_keys($this->errorRenderers);
        $contentType = $this->defaultErrorRendererContentType;

        if ($header !== '' && \count($priorities) !== 0) {
            try {
                $selected = $this->negotiator->getBest($header, $priorities);

                if ($selected instanceof BaseAccept) {
                    $contentType = $selected->getType();
                }
            } catch (NegotiateException) {
                // @ignoreException
            }
        }

        if (str_contains($contentType, '/*+')) {
            $contentType = str_replace('/*+', '/', $contentType);
        }

        return $contentType;
    }

    protected function inCli(): bool
    {
        return \PHP_SAPI === 'cli';
    }

    protected function writeToErrorLog(): void
    {
        $logLevel = $this->getLogLevel();
        $logContext = [
            'http_method' => $this->request->getMethod(),
            'request_uri' => (string) $this->request->getUri(),
            'level_name' => mb_strtoupper($logLevel),
        ];

        $renderer = $this->determineLogRenderer();
        $message = $renderer($this->exception, $this->logErrorDetails);
        if (!$this->displayErrorDetails) {
            $message .= "\nTips: To display error details in HTTP response ";
            $message .= 'set "displayErrorDetails" to true in the ErrorHandler constructor.';
        }

        $this->logger->log($logLevel, $message, $logContext);
    }

    /**
     * @return callable(Throwable, bool): string
     */
    protected function determineLogRenderer(): callable
    {
        return $this->callableResolver->resolve($this->logErrorRenderer);
    }

    final protected function getLogLevel(): string
    {
        if (
            $this->exception instanceof ErrorException
            && \array_key_exists($this->exception->getSeverity(), $this->errorToLogLevelMap)
        ) {
            return $this->errorToLogLevelMap[$this->exception->getSeverity()];
        }

        return LogLevel::ERROR;
    }
}
