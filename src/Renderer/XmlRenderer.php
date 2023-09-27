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

use Throwable;

class XmlRenderer extends AbstractRenderer
{
    protected bool $prettify = true;

    public function setPrettify(bool $prettify): void
    {
        $this->prettify = $prettify;
    }

    public function __invoke(Throwable $exception, bool $displayErrorDetails): string
    {
        $xmlTag = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>\n";

        $errorParts = [
            '<error>',
            '  <message>' . $this->createCdataSection($this->getErrorTitle($exception)) . '</message>',
        ];

        if ($displayErrorDetails) {
            do {
                $errorParts[] = '  <exception>';
                $errorParts[] = '    <type>' . $exception::class . '</type>';
                $errorParts[] = '    <code>' . $exception->getCode() . '</code>';
                $errorParts[] = '    <message>' . $this->createCdataSection($exception->getMessage()) . '</message>';
                $errorParts[] = '    <file>' . $exception->getFile() . '</file>';
                $errorParts[] = '    <line>' . $exception->getLine() . '</line>';
                $errorParts[] = '  </exception>';

                $exception = $exception->getPrevious();
            } while ($exception !== null);
        }
        $errorParts[] = '</error>';

        if ($this->prettify) {
            return $xmlTag . implode("\n", $errorParts);
        }

        return $xmlTag . implode(
            '',
            array_map(
                static fn(string $line): string => ltrim($line, ' '),
                $errorParts,
            ),
        );
    }

    private function createCdataSection(string $content): string
    {
        return sprintf('<![CDATA[%s]]>', str_replace(']]>', ']]]]><![CDATA[>', $content));
    }
}
