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

/**
 * Whoops custom XML exception renderer.
 */
class XmlRenderer extends XmlResponseHandler
{
    use RendererTrait;

    /**
     * XmlHandler constructor.
     *
     * @param string $defaultTitle
     */
    public function __construct(string $defaultTitle = 'Slim Application error')
    {
        $this->defaultTitle = $defaultTitle;

        $this->addTraceToOutput(true);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(): int
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
     * @param mixed[] $data
     *
     * @return string
     */
    protected function getFormattedXml(array $data): string
    {
        /** @var \SimpleXMLElement $root */
        $root = \simplexml_load_string('<?xml version="1.0" encoding="utf-8"?><root />');
        $this->addDataNodes($root->addChild('error'), $data, 'exception');

        /** @var \SimpleXMLElement $rootDocument */
        $rootDocument = \dom_import_simplexml($root);
        $dom = $rootDocument->ownerDocument;
        $dom->formatOutput = true;

        return $dom->saveXML();
    }

    /**
     * Transform data to XML nodes.
     *
     * @param \SimpleXMLElement $node
     * @param mixed[]           $data
     * @param string            $nodeKey
     *
     * @return \SimpleXMLElement
     */
    protected function addDataNodes(\SimpleXMLElement $node, array $data, string $nodeKey): \SimpleXMLElement
    {
        foreach ($data as $key => $value) {
            if (\is_numeric($key)) {
                $key = $nodeKey . '_' . $key;
            }
            /** @var string $key */
            $key = \preg_replace('/[^a-z0-9\-_.:]/i', '_', $key);

            if (\is_array($value)) {
                $this->addDataNodes($node->addChild($key), $value, $key);
            } else {
                if (\is_object($value)) {
                    $value = \get_class($value);
                } elseif (!\is_scalar($value)) {
                    $value = \gettype($value);
                }

                $node->addChild($key, \str_replace('&', '&amp;', \print_r($value, true)));
            }
        }

        return $node;
    }
}
