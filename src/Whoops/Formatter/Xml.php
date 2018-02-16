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

namespace Jgut\Slim\Exception\Whoops\Formatter;

use Jgut\Slim\Exception\ExceptionFormatter;
use Jgut\Slim\Exception\Whoops\Inspector;
use Whoops\Handler\Handler;
use Whoops\Handler\XmlResponseHandler;

/**
 * Whoops custom XML HTTP exception formatter.
 */
class Xml extends XmlResponseHandler implements ExceptionFormatter
{
    use FormatterTrait;

    /**
     * XmlHandler constructor.
     */
    public function __construct()
    {
        $this->addTraceToOutput(true);
    }

    /**
     * {@inheritdoc}
     */
    public function getContentTypes(): array
    {
        return [
            'application/xml',
            'text/xml',
            'application/x-xml',
            'application/*+xml',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function handle(): int
    {
        /** @var \Jgut\HttpException\HttpException $exception */
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
     * @param array $data
     *
     * @return string
     */
    protected function getFormattedXml(array $data): string
    {
        $root = \simplexml_load_string('<?xml version="1.0" encoding="utf-8"?><root />');
        $this->addDataNodes($root->addChild('error'), $data, 'exception');

        $dom = \dom_import_simplexml($root)->ownerDocument;
        $dom->formatOutput = true;

        return $dom->saveXML();
    }

    /**
     * Transform data to XML nodes.
     *
     * @param \SimpleXMLElement $node
     * @param array             $data
     * @param string|null       $nodeKey
     *
     * @return \SimpleXMLElement
     */
    protected function addDataNodes(\SimpleXMLElement $node, array $data, string $nodeKey = null): \SimpleXMLElement
    {
        foreach ($data as $key => $value) {
            if (\is_numeric($key)) {
                $key = $nodeKey . '_' . (string) $key;
            }
            $key = \preg_replace('/[^a-z0-9\-\_\.\:]/i', '_', $key);

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
