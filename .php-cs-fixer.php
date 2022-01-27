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

use Jgut\CS\Fixer\FixerConfig74;
use PhpCsFixer\Finder;

$header = <<<'HEADER'
slim-exception (https://github.com/juliangut/slim-exception).
Slim exception handling.

@license BSD-3-Clause
@link https://github.com/juliangut/slim-exception
@author Julián Gutiérrez <juliangut@gmail.com>
HEADER;

$finder = Finder::create()
    ->ignoreDotFiles(false)
    ->exclude(['build', 'vendor'])
    ->in(__DIR__)
    ->name('.php-cs-fixer.php');

return (new FixerConfig74())
    ->setHeader($header)
    ->enablePhpUnitRules()
    ->setFinder($finder);
