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

namespace Jgut\Slim\Exception\Handler\Whoops;

use Fig\Http\Message\StatusCodeInterface;
use Jgut\Slim\Exception\HttpException;
use Whoops\Exception\FrameCollection;
use Whoops\Handler\Handler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Util\Misc;
use Whoops\Util\TemplateHelper;

/**
 * Whoops custom HTML response handler.
 */
class HtmlHandler extends PrettyPageHandler
{
    use DumperTrait;

    /**
     * The name of the custom css file.
     *
     * @var string
     */
    protected $customCss;

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function handle(): int
    {
        $helper = new TemplateHelper();

        $templateFile = $this->getResource('views/layout.html.php');
        $cssFile = $this->getResource('css/whoops.base.css');
        $zeptoFile = $this->getResource('js/zepto.min.js');
        $clipboard = $this->getResource('js/clipboard.min.js');
        $jsFile = $this->getResource('js/whoops.base.js');

        $code = $this->getExceptionCode();
        $inspector = $this->getInspector();
        $frames = $this->detectApplicationFrames($this->filterInternalFrames($inspector->getFrames()));

        // List of variables that will be passed to the layout template.
        $vars = [
            'preface' => '',

            'page_title' => $this->getPageTitle(),

            'stylesheet' => file_get_contents($cssFile),
            'zepto' => file_get_contents($zeptoFile),
            'clipboard' => file_get_contents($clipboard),
            'javascript' => file_get_contents($jsFile),

            'header' => $this->getResource('views/header.html.php'),
            'header_outer' => $this->getResource('views/header_outer.html.php'),
            'frame_list' => $this->getResource('views/frame_list.html.php'),
            'frames_description' => $this->getResource('views/frames_description.html.php'),
            'frames_container' => $this->getResource('views/frames_container.html.php'),
            'panel_details' => $this->getResource('views/panel_details.html.php'),
            'panel_details_outer' => $this->getResource('views/panel_details_outer.html.php'),
            'panel_left' => $this->getResource('views/panel_left.html.php'),
            'panel_left_outer' => $this->getResource('views/panel_left_outer.html.php'),
            'frame_code' => $this->getResource('views/frame_code.html.php'),
            'env_details' => $this->getResource('views/env_details.html.php'),

            'title' => $this->getPageTitle(),
            'name' => explode('\\', $inspector->getExceptionName()),
            'message' => $inspector->getException()->getMessage(),
            'code' => $code,
            'plain_exception' => $this->formatExceptionText(),
            'frames' => $frames,
            'has_frames' => (bool) count($frames),
            'handler' => $this,
            'handlers' => $this->getRun()->getHandlers(),

            'active_frames_tab' => count($frames) && $frames[0]->isApplication() ? 'application' : 'all',
            'has_frames_tabs' => $this->getApplicationPaths(),

            'tables' => [
                'GET Data' => $_GET,
                'POST Data' => $_POST,
                'Files' => $_FILES,
                'Cookies' => $_COOKIE,
                'Session' => $_SESSION ?? [],
                'Server/Request Data' => $_SERVER,
                'Environment Variables' => $_ENV,
            ],
        ];

        if ($this->customCss) {
            $vars['stylesheet'] .= file_get_contents($this->getResource($this->customCss));
        }

        $extraTables = array_map(function ($table) use ($inspector) {
            return $table instanceof \Closure ? $table($inspector) : $table;
        }, $this->getDataTables());
        $vars['tables'] = array_merge($extraTables, $vars['tables']);

        $helper->setVariables($vars);
        $helper->render($templateFile);

        return Handler::QUIT;
    }

    /**
     * Get exception code.
     *
     * @return string
     */
    protected function getExceptionCode(): string
    {
        /* @var HttpException $exception */
        $exception = $this->getException();

        while ($exception instanceof HttpException
            && $exception->getHttpStatusCode() === StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR
            && $exception->getPrevious() !== null) {
            $exception = $exception->getPrevious();
        }

        $code = $exception->getCode();
        if ($exception instanceof \ErrorException) {
            // ErrorExceptions wrap the php-error types within the 'severity' property
            $code = Misc::translateErrorCode($exception->getSeverity());
        }

        return (string) $code;
    }

    /**
     * Detect frames that belong to the application.
     *
     * @param FrameCollection $frames
     *
     * @return FrameCollection
     */
    protected function detectApplicationFrames(FrameCollection $frames): FrameCollection
    {
        if ($this->getApplicationPaths()) {
            foreach ($frames as $frame) {
                foreach ($this->getApplicationPaths() as $path) {
                    if (strpos($frame->getFile(), $path) === 0) {
                        $frame->setApplication(true);
                        break;
                    }
                }
            }
        }

        return $frames;
    }

    /**
     * Format plain exception text.
     *
     * @return string
     */
    protected function formatExceptionText(): string
    {
        /* @var HttpException $exception */
        $exception = $this->getException();
        $inspector = $this->getInspector();

        $plain = <<<TEXT
({$exception->getIdentifier()}) {$inspector->getExceptionName()} thrown with message "{$exception->getMessage()}"


Stacktrace:

TEXT;

        $frames = $inspector->getFrames();
        /* @var \Whoops\Exception\Frame $frame */
        foreach ($frames as $i => $frame) {
            $plain .= '#' . (count($frames) - $i - 1) . ' ';
            $plain .= $frame->getClass() ?: '';
            $plain .= $frame->getClass() && $frame->getFunction() ? ':' : '';
            $plain .= $frame->getFunction() ?: '';
            $plain .= ' in ';
            $plain .= ($frame->getFile() ?: '<#unknown>');
            $plain .= ':';
            $plain .= (int) $frame->getLine() . "\n";
        }

        return $plain;
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomCss($name)
    {
        $this->customCss = $name;
    }
}
