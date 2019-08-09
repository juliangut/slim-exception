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

use Slim\Interfaces\ErrorRendererInterface;

/**
 * XML exception renderer.
 */
class XmlRenderer implements ErrorRendererInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(\Throwable $exception, bool $displayErrorDetails): string
    {
        $output = '<' . '?xml version="1.0" encoding="UTF-8" standalone="yes"?' . ">\n";
        $output .= "<error>\n  <message>" . $this->createCdataSection($exception->getMessage()) . "</message>\n";

        if ($displayErrorDetails) {
            $output .= "  <trace>\n";

            do {
                $output .= "    <exception>\n";
                $output .= '      <type>' . \get_class($exception) . "</type>\n";
                $output .= '      <code>' . $exception->getCode() . "</code>\n";
                $output .= '      <message>' . $this->createCdataSection($exception->getMessage()) . "</message>\n";
                $output .= '      <file>' . $exception->getFile() . "</file>\n";
                $output .= '      <line>' . $exception->getLine() . "</line>\n";
                $output .= "    </exception>\n";
            } while ($exception = $exception->getPrevious());

            $output .= "  </trace>\n";
        }

        $output .= '</error>';

        return $output;
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
