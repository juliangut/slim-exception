<?php

/*
 * (c) 2017-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-exception
 */

declare(strict_types=1);

namespace Jgut\Slim\Exception\Whoops\Renderer;

use DOMDocument;
use DOMElement;
use SimpleXMLElement;
use Whoops\Exception\Frame;
use Whoops\Handler\Handler;
use Whoops\Handler\XmlResponseHandler;

class XmlRenderer extends XmlResponseHandler
{
    use RendererTrait;

    protected bool $prettify = true;

    public function __construct(
        protected string $defaultTitle = 'Slim Application error',
    ) {
        $this->addTraceToOutput(true);
    }

    public function setPrettify(bool $prettify): void
    {
        $this->prettify = $prettify;
    }

    public function handle()
    {
        /** @var bool $addTrace */
        $addTrace = $this->addTraceToOutput();

        /** @var list<callable(Frame): bool> $frameFilters */
        $frameFilters = array_values($this->getRun()->getFrameFilters());

        $response = $this->getExceptionData($this->getInspector(), $addTrace, $frameFilters);

        $output = $this->getFormattedXml($response);

        echo $output; // @phpstan-ignore-line

        return Handler::QUIT;
    }

    /**
     * @param ExceptionData $data
     */
    protected function getFormattedXml(array $data): string
    {
        $xmlTemplate = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><error />';

        /** @var SimpleXMLElement $root */
        $root = simplexml_load_string($xmlTemplate);
        $this->addDataNodes($root, $data, 'exception');

        /** @var DOMElement $rootDocument */
        $rootDocument = dom_import_simplexml($root);
        /** @var DOMDocument $dom */
        $dom = $rootDocument->ownerDocument;
        $dom->formatOutput = $this->prettify;

        $xmlOutput = $dom->saveXML();

        return \is_string($xmlOutput) ? $xmlOutput : $xmlTemplate;
    }

    /**
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
                /** @var SimpleXMLElement $childNode */
                $childNode = $node->addChild($key);
                $this->addDataNodes($childNode, $value, $key);
            } else {
                if (\is_object($value)) {
                    $value = $value::class;
                } elseif (!\is_scalar($value)) {
                    $value = \gettype($value);
                }

                $value = str_replace('&', '&amp;', print_r($value, true));

                if ($key === 'message') {
                    /** @var SimpleXMLElement $childNode */
                    $childNode = $node->addChild($key);
                    /** @var DOMElement $child */
                    $child = dom_import_simplexml($childNode);
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
