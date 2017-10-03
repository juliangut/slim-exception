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

namespace Jgut\Slim\Exception\Whoops;

use Fig\Http\Message\StatusCodeInterface;
use Jgut\Slim\Exception\HttpException;
use Whoops\Exception\Inspector as BaseInspector;

/**
 * Custom Whoops inspector.
 */
class Inspector extends BaseInspector
{
    /**
     * Inspector constructor.
     *
     * @param HttpException $exception
     */
    public function __construct(HttpException $exception)
    {
        while ($exception instanceof HttpException
            && $exception->getStatusCode() === StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR
            && $exception->getPrevious() !== null) {
            $exception = $exception->getPrevious();
        }

        parent::__construct($exception);
    }
}
