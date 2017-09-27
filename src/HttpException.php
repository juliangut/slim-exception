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
class HttpException extends \DomainException
{
    /**
     * Unique error identifier.
     *
     * @var string
     */
    protected $identifier;

    /**
     * Exception description.
     *
     * @var string
     */
    protected $description;

    /**
     * HTTP status code.
     *
     * @var int
     */
    protected $statusCode;

    /**
     * Exception constructor.
     *
     * @param string          $message
     * @param string          $description
     * @param int             $code
     * @param int             $httpStatusCode
     * @param \Throwable|null $previous
     */
    public function __construct(
        string $message,
        string $description,
        int $code,
        int $httpStatusCode,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->identifier = ShortUuid::uuid4();
        $this->description = $description;
        $this->statusCode = $httpStatusCode;
    }

    /**
     * Get exception unique identifier.
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * Get exception description.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Get HTTP status code.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
