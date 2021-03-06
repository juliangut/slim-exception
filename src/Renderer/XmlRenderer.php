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

namespace Jgut\Slim\Exception\Renderer;

/**
 * XML exception renderer.
 */
class XmlRenderer extends AbstractRenderer
{
    /**
     * @var bool
     */
    protected $prettify = true;

    /**
     * @param bool $prettify
     */
    public function setPrettify(bool $prettify): void
    {
        $this->prettify = $prettify;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(\Throwable $exception, bool $displayErrorDetails): string
    {
        $xmlTag = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";

        $errorParts = [
            '<error>',
            '  <message>' . $this->createCdataSection($this->getErrorTitle($exception)) . '</message>',
        ];

        if ($displayErrorDetails) {
            do {
                $errorParts[] = '  <exception>';
                $errorParts[] = '    <type>' . \get_class($exception) . '</type>';
                $errorParts[] = '    <code>' . $exception->getCode() . '</code>';
                $errorParts[] = '    <message>' . $this->createCdataSection($exception->getMessage()) . '</message>';
                $errorParts[] = '    <file>' . $exception->getFile() . '</file>';
                $errorParts[] = '    <line>' . $exception->getLine() . '</line>';
                $errorParts[] = '  </exception>';
            } while ($exception = $exception->getPrevious());
        }
        $errorParts[] = '</error>';

        if ($this->prettify) {
            return $xmlTag . \implode("\n", $errorParts);
        }

        return $xmlTag . \implode(
            '',
            \array_map(
                static function (string $line): string {
                    return \ltrim($line, ' ');
                },
                $errorParts
            )
        );
    }

    /**
     * Returns a CDATA section with the given content.
     *
     * @param string $content
     *
     * @return string
     */
    private function createCdataSection(string $content): string
    {
        return \sprintf('<![CDATA[%s]]>', \str_replace(']]>', ']]]]><![CDATA[>', $content));
    }
}
