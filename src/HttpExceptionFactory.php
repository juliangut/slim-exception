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

use Fig\Http\Message\StatusCodeInterface;

/**
 * HTTP exception factory.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class HttpExceptionFactory
{
    /**
     * (400) Generic bad request error exception.
     *
     * @param string     $message
     * @param int        $code
     * @param \Throwable $previous
     *
     * @return HttpException
     */
    public static function badRequest(
        string $message = null,
        int $code = null,
        \Throwable $previous = null
    ): HttpException {
        return static::create(
            $message ?: 'Bad request',
            $code ?? StatusCodeInterface::STATUS_BAD_REQUEST,
            StatusCodeInterface::STATUS_BAD_REQUEST,
            $previous
        );
    }

    /**
     * (401) Generic unauthorized error exception.
     *
     * @param string     $message
     * @param int        $code
     * @param \Throwable $previous
     *
     * @return HttpException
     */
    public static function unauthorized(
        string $message = null,
        int $code = null,
        \Throwable $previous = null
    ): HttpException {
        return static::create(
            $message ?: 'Unauthorized',
            $code ?? StatusCodeInterface::STATUS_UNAUTHORIZED,
            StatusCodeInterface::STATUS_UNAUTHORIZED,
            $previous
        );
    }

    /**
     * (403) Generic forbidden error exception.
     *
     * @param string     $message
     * @param int        $code
     * @param \Throwable $previous
     *
     * @return HttpException
     */
    public static function forbidden(
        string $message = null,
        int $code = null,
        \Throwable $previous = null
    ): HttpException {
        return static::create(
            $message ?: 'Forbidden',
            $code ?? StatusCodeInterface::STATUS_FORBIDDEN,
            StatusCodeInterface::STATUS_FORBIDDEN,
            $previous
        );
    }

    /**
     * (404) Generic not found error exception.
     *
     * @param string     $message
     * @param int        $code
     * @param \Throwable $previous
     *
     * @return HttpException
     */
    public static function notFound(
        string $message = null,
        int $code = null,
        \Throwable $previous = null
    ): HttpException {
        return static::create(
            $message ?: 'Not found',
            $code ?? StatusCodeInterface::STATUS_NOT_FOUND,
            StatusCodeInterface::STATUS_NOT_FOUND,
            $previous
        );
    }

    /**
     * (405) Generic method not allowed error exception.
     *
     * @param string     $message
     * @param int        $code
     * @param \Throwable $previous
     *
     * @return HttpException
     */
    public static function methodNotAllowed(
        string $message = null,
        int $code = null,
        \Throwable $previous = null
    ): HttpException {
        return static::create(
            $message ?: 'Method not allowed',
            $code ?? StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED,
            StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED,
            $previous
        );
    }

    /**
     * (406) Generic not acceptable error exception.
     *
     * @param string     $message
     * @param int        $code
     * @param \Throwable $previous
     *
     * @return HttpException
     */
    public static function notAcceptable(
        string $message = null,
        int $code = null,
        \Throwable $previous = null
    ): HttpException {
        return static::create(
            $message ?: 'Not acceptable',
            $code ?? StatusCodeInterface::STATUS_NOT_ACCEPTABLE,
            StatusCodeInterface::STATUS_NOT_ACCEPTABLE,
            $previous
        );
    }

    /**
     * (409) Generic conflict error exception.
     *
     * @param string     $message
     * @param int        $code
     * @param \Throwable $previous
     *
     * @return HttpException
     */
    public static function conflict(
        string $message = null,
        int $code = null,
        \Throwable $previous = null
    ): HttpException {
        return static::create(
            $message ?: 'Conflict',
            $code ?? StatusCodeInterface::STATUS_CONFLICT,
            StatusCodeInterface::STATUS_CONFLICT,
            $previous
        );
    }

    /**
     * (410) Generic gone error exception.
     *
     * @param string     $message
     * @param int        $code
     * @param \Throwable $previous
     *
     * @return HttpException
     */
    public static function gone(
        string $message = null,
        int $code = null,
        \Throwable $previous = null
    ): HttpException {
        return static::create(
            $message ?: 'Gone',
            $code ?? StatusCodeInterface::STATUS_GONE,
            StatusCodeInterface::STATUS_GONE,
            $previous
        );
    }

    /**
     * (415) Generic unsupported media type error exception.
     *
     * @param string     $message
     * @param int        $code
     * @param \Throwable $previous
     *
     * @return HttpException
     */
    public static function unsupportedMediaType(
        string $message = null,
        int $code = null,
        \Throwable $previous = null
    ): HttpException {
        return static::create(
            $message ?: 'Unsupported media type',
            $code ?? StatusCodeInterface::STATUS_UNSUPPORTED_MEDIA_TYPE,
            StatusCodeInterface::STATUS_UNSUPPORTED_MEDIA_TYPE,
            $previous
        );
    }

    /**
     * (422) Generic unprocessable entity error exception.
     *
     * @param string     $message
     * @param int        $code
     * @param \Throwable $previous
     *
     * @return HttpException
     */
    public static function unprocessableEntity(
        string $message = null,
        int $code = null,
        \Throwable $previous = null
    ): HttpException {
        return static::create(
            $message ?: 'Unprocessable entity',
            $code ?? StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
            StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
            $previous
        );
    }

    /**
     * (429) Generic too many requests error exception.
     *
     * @param string     $message
     * @param int        $code
     * @param \Throwable $previous
     *
     * @return HttpException
     */
    public static function tooManyRequests(
        string $message = null,
        int $code = null,
        \Throwable $previous = null
    ): HttpException {
        return static::create(
            $message ?: 'Too many requests',
            $code ?? StatusCodeInterface::STATUS_TOO_MANY_REQUESTS,
            StatusCodeInterface::STATUS_TOO_MANY_REQUESTS,
            $previous
        );
    }

    /**
     * (500) Generic internal server error exception.
     *
     * @param string     $message
     * @param int        $code
     * @param \Throwable $previous
     *
     * @return HttpException
     */
    public static function internalServerError(
        string $message = null,
        int $code = null,
        \Throwable $previous = null
    ): HttpException {
        return static::create(
            $message ?: 'Internal server error',
            $code ?? StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR,
            StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR,
            $previous
        );
    }

    /**
     * (501) Generic not implemented error exception.
     *
     * @param string     $message
     * @param int        $code
     * @param \Throwable $previous
     *
     * @return HttpException
     */
    public static function notImplemented(
        string $message = null,
        int $code = null,
        \Throwable $previous = null
    ): HttpException {
        return static::create(
            $message ?: 'Not implemented',
            $code ?? StatusCodeInterface::STATUS_NOT_IMPLEMENTED,
            StatusCodeInterface::STATUS_NOT_IMPLEMENTED,
            $previous
        );
    }

    /**
     * Get new HTTP exception.
     *
     * @param string     $message
     * @param int        $code
     * @param int        $httpStatusCode
     * @param \Throwable $previous
     *
     * @return HttpException
     */
    public static function create(
        string $message,
        int $code,
        int $httpStatusCode,
        \Throwable $previous = null
    ): HttpException {
        return new HttpException($message, $code, $httpStatusCode, $previous);
    }
}
