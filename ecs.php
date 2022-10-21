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

use Jgut\ECS\Config\ConfigSet74;
use SlevomatCodingStandard\Sniffs\Exceptions\ReferenceThrowableOnlySniff;
use Symplify\EasyCodingStandard\Config\ECSConfig;

$header = <<<'HEADER'
slim-exception (https://github.com/juliangut/slim-exception).
Slim exception handling.

@license BSD-3-Clause
@link https://github.com/juliangut/slim-exception
@author Julián Gutiérrez <juliangut@gmail.com>
HEADER;

return static function (ECSConfig $ecsConfig) use ($header): void {
    $ecsConfig->paths([
        __FILE__,
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    $configSet = new ConfigSet74();

    $configSet
        ->setHeader($header)
        ->enablePhpUnitRules()
        ->setAdditionalSkips([
            ReferenceThrowableOnlySniff::class . '.ReferencedGeneralException' => [
                __DIR__ . '/src/ExceptionHandler.php',
            ],
        ])
        ->configure($ecsConfig);
};
