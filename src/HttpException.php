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

namespace Jgut\Slim\Exception;

use PascalDeVink\ShortUuid\ShortUuid;

/**
 * HTTP exception class.
 */
class HttpException extends \RuntimeException
{
    /**
     * Unique error identifier.
     *
     * @var string
     */
    protected $identifier;

    /**
     * HTTP status code.
     *
     * @var int
     */
    protected $httpStatusCode;

    /**
     * Exception constructor.
     *
     * @param string     $message
     * @param int        $code
     * @param int        $httpStatusCode
     * @param \Throwable $previous
     */
    public function __construct(string $message, int $code, int $httpStatusCode, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->identifier = ShortUuid::uuid4();
        $this->httpStatusCode = $httpStatusCode;
    }

    /**
     * Get error unique identifier.
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * Get HTTP status code.
     *
     * @return int
     */
    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }
}
