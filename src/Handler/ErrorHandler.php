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

namespace Jgut\Slim\Exception\Handler;

use Jgut\Slim\Exception\Renderer\HtmlRenderer;
use Jgut\Slim\Exception\Renderer\JsonRenderer;
use Jgut\Slim\Exception\Renderer\TextRenderer;
use Jgut\Slim\Exception\Renderer\XmlRenderer;
use Jgut\Slim\Exception\Whoops\Renderer\TextRenderer as WhoopsTextRenderer;
use Negotiation\BaseAccept;
use Negotiation\Negotiator;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;
use Slim\Handlers\ErrorHandler as SlimErrorHandler;
use Slim\Interfaces\CallableResolverInterface;

/**
 * HTTP exception handler.
 */
class ErrorHandler extends SlimErrorHandler
{
    use LoggerAwareTrait;

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
     * @var string[]
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
        'text/plain' => TextRenderer::class,
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
        parent::__construct($callableResolver, $responseFactory);

        $this->negotiator = $negotiator;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(
        ServerRequestInterface $request,
        \Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): ResponseInterface {
        if ($this->inCli()) {
            $request = $request->withHeader('Accept', 'text/plain');
        }

        return parent::__invoke($request, $exception, $displayErrorDetails, $logErrors, $logErrorDetails);
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
    protected function determineContentType(ServerRequestInterface $request): ?string
    {
        $header = \trim($request->getHeaderLine('Accept'));
        $priorities = \array_keys($this->errorRenderers);
        $contentType = $this->defaultErrorRendererContentType;

        if ($header !== '') {
            try {
                $selected = $this->negotiator->getBest($header, $priorities);

                if ($selected instanceof BaseAccept) {
                    $contentType = $selected->getType();
                }
                // @codeCoverageIgnoreStart
            } catch (\Exception $exception) {
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
            // TODO 'exception_id' => $this->exception->getIdentifier(),
            'http_method' => $this->request->getMethod(),
            'request_uri' => (string) $this->request->getUri(),
            'level_name' => \strtoupper($logLevel),
            'stacktrace' => $this->getStackTrace(),
        ];

        $this->logger->log($logLevel, $this->exception->getMessage(), $logContext);
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

    /**
     * Get exception stack trace.
     *
     * @return string
     */
    protected function getStackTrace(): string
    {
        if (!$this->logErrorDetails) {
            return '';
        }

        if (!\interface_exists('\Whoops\RunInterface')) {
            // @codeCoverageIgnoreStart
            return $this->exception->getTraceAsString();
            // @codeCoverageIgnoreEnd
        }

        $renderer = new WhoopsTextRenderer();
        $renderer->setException($this->exception);
        $exceptionParts = \explode("\n", \rtrim($renderer->generateResponse(), "\n"));

        if (\count($exceptionParts) !== 1) {
            $exceptionParts = \array_filter(\array_splice($exceptionParts, 2));
        }

        return \implode("\n", $exceptionParts);
    }
}
