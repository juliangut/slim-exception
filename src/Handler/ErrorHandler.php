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

namespace Jgut\Slim\Exception\Handler;

use Jgut\Slim\Exception\Renderer\HtmlRenderer;
use Jgut\Slim\Exception\Renderer\JsonRenderer;
use Jgut\Slim\Exception\Renderer\PlainTextRenderer;
use Jgut\Slim\Exception\Renderer\XmlRenderer;
use Negotiation\BaseAccept;
use Negotiation\Negotiator;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;
use Slim\Handlers\ErrorHandler as SlimErrorHandler;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\ErrorRendererInterface;

/**
 * exception handler.
 */
class ErrorHandler extends SlimErrorHandler
{
    use LoggerAwareTrait;

    /**
     * Default error renderer for logs.
     *
     * @var ErrorRendererInterface|string|callable
     */
    protected $logErrorRenderer = PlainTextRenderer::class;

    /**
     * PHP to PSR3 error map.
     *
     * @var array
     */
    private $errorToLogLevelMap = [
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
     * Content type negotiator.
     *
     * @var Negotiator
     */
    protected $negotiator;

    /**
     * @var array<string, string|ErrorRendererInterface>
     */
    protected $errorRenderers = [
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

    /**
     * ErrorHandler constructor.
     *
     * @param CallableResolverInterface $callableResolver
     * @param ResponseFactoryInterface  $responseFactory
     * @param Negotiator                $negotiator
     */
    public function __construct(
        CallableResolverInterface $callableResolver,
        ResponseFactoryInterface $responseFactory,
        Negotiator $negotiator
    ) {
        $this->negotiator = $negotiator;

        parent::__construct($callableResolver, $responseFactory);
    }

    /**
     * Set error renderers.
     *
     * @param array<string, string|ErrorRendererInterface> $errorRenderers
     */
    public function setErrorRenderers(array $errorRenderers): void
    {
        $this->errorRenderers = [];

        foreach ($errorRenderers as $contentType => $errorRenderer) {
            $this->setErrorRenderer($contentType, $errorRenderer);
        }
    }

    /**
     * Set error renderer.
     *
     * @param string                        $contentType
     * @param string|ErrorRendererInterface $errorRenderer
     */
    public function setErrorRenderer(string $contentType, $errorRenderer): void
    {
        $this->errorRenderers[$contentType] = $errorRenderer;
    }

    /**
     * {@inheritdoc}
     */
    protected function determineContentType(ServerRequestInterface $request): ?string
    {
        if ($this->inCli()) {
            return 'text/plain';
        }

        $header = \trim($request->getHeaderLine('Accept'));
        $priorities = \array_keys($this->errorRenderers);
        $contentType = $this->defaultErrorRendererContentType;

        if ($header !== '' && \count($priorities) !== 0) {
            try {
                $selected = $this->negotiator->getBest($header, $priorities);

                if ($selected instanceof BaseAccept) {
                    $contentType = $selected->getType();
                }
                // @codeCoverageIgnoreStart
            } catch (\Throwable $exception) {
                // @ignoreException
            }
            // @codeCoverageIgnoreEnd
        }

        if (\strpos($contentType, '/*+') !== false) {
            $contentType = \str_replace('/*+', '/', $contentType);
        }

        return $contentType;
    }

    /**
     * Check if running in CLI.
     *
     * @return bool
     */
    protected function inCli(): bool
    {
        return \PHP_SAPI === 'cli';
    }

    /**
     * {@inheritdoc}
     */
    protected function writeToErrorLog(): void
    {
        if ($this->logger === null) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        $logLevel = $this->getLogLevel();
        $logContext = [
            'http_method' => $this->request->getMethod(),
            'request_uri' => (string) $this->request->getUri(),
            'level_name' => \strtoupper($logLevel),
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
     * Determine log renderer.
     *
     * @return callable
     */
    protected function determineLogRenderer(): callable
    {
        return $this->callableResolver->resolve($this->logErrorRenderer);
    }

    /**
     * Get log level.
     *
     * @return string
     */
    final protected function getLogLevel(): string
    {
        if ($this->exception instanceof \ErrorException
            && \array_key_exists($this->exception->getSeverity(), $this->errorToLogLevelMap)
        ) {
            return $this->errorToLogLevelMap[$this->exception->getSeverity()];
        }

        return LogLevel::ERROR;
    }
}
