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

namespace Jgut\Slim\Exception\Whoops\Renderer;

use Jgut\Slim\Exception\Whoops\Inspector;
use Whoops\Handler\Handler;
use Whoops\Handler\XmlResponseHandler;
use DOMDocument;
use DOMElement;
use SimpleXMLElement;

class XmlRenderer extends XmlResponseHandler
{
    use RendererTrait;

    /**
     * @var bool
     */
    protected $prettify = true;

    public function __construct(string $defaultTitle = 'Slim Application error')
    {
        $this->defaultTitle = $defaultTitle;

        $this->addTraceToOutput(true);
    }

    public function setPrettify(bool $prettify): void
    {
        $this->prettify = $prettify;
    }

    /**
     * @inheritDoc
     */
    public function handle()
    {
        $exception = $this->getException();

        $inspector = new Inspector($exception);
        $this->setInspector($inspector);

        /** @var bool $addTrace */
        $addTrace = $this->addTraceToOutput();

        $error = $this->getExceptionData($inspector, $addTrace);

        echo $this->getFormattedXml($error);

        return Handler::QUIT;
    }

    /**
     * Get formatted XML exception.
     *
     * @param array<mixed> $data
     */
    protected function getFormattedXml(array $data): string
    {
        $xmlTemplate = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><error />';

        /** @var SimpleXMLElement $root */
        $root = simplexml_load_string($xmlTemplate);
        $this->addDataNodes($root, $data, 'exception');

        /** @var SimpleXMLElement $rootDocument */
        $rootDocument = dom_import_simplexml($root);
        $dom = $rootDocument->ownerDocument;
        $dom->formatOutput = $this->prettify;

        $xmlOutput = $dom->saveXML();

        return \is_string($xmlOutput) ? $xmlOutput : $xmlTemplate;
    }

    /**
     * Transform data to XML nodes.
     *
     * @param array<mixed> $data
     */
    protected function addDataNodes(SimpleXMLElement $node, array $data, string $nodeKey): SimpleXMLElement
    {
        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                $key = $nodeKey . '_' . $key;
            }
            /** @var string $key */
            $key = preg_replace('/[^a-z0-9\-_.:]/i', '_', $key);

            if (\is_array($value)) {
                $this->addDataNodes($node->addChild($key), $value, $key);
            } else {
                if (\is_object($value)) {
                    $value = \get_class($value);
                } elseif (!is_scalar($value)) {
                    $value = \gettype($value);
                }

                $value = str_replace('&', '&amp;', print_r($value, true));

                if ($key === 'message') {
                    /** @var DOMElement $child */
                    $child = dom_import_simplexml($node->addChild($key));
                    /** @var DOMDocument $document */
                    $document = $child->ownerDocument;

                    $child->appendChild($document->createCDATASection($value));
                } else {
                    $node->addChild($key, $value);
                }
            }
        }

        return $node;
    }
}
