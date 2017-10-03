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
     * @param string|null     $message
     * @param string|null     $description
     * @param int|null        $code
     * @param \Throwable|null $previous
     *
     * @return HttpException
     */
    public static function badRequest(
        string $message = null,
        string $description = null,
        int $code = null,
        \Throwable $previous = null
    ): HttpException {
        return static::create(
            $message ? trim($message) : 'Bad request',
            $description ? trim($description) : '',
            $code ?? StatusCodeInterface::STATUS_BAD_REQUEST,
            StatusCodeInterface::STATUS_BAD_REQUEST,
            $previous
        );
    }

    /**
     * (401) Generic unauthorized error exception.
     *
     * @param string|null     $message
     * @param string|null     $description
     * @param int|null        $code
     * @param \Throwable|null $previous
     *
     * @return HttpException
     */
    public static function unauthorized(
        string $message = null,
        string $description = null,
        int $code = null,
        \Throwable $previous = null
    ): HttpException {
        return static::create(
            $message ? trim($message) : 'Unauthorized',
            $description ? trim($description) : '',
            $code ?? StatusCodeInterface::STATUS_UNAUTHORIZED,
            StatusCodeInterface::STATUS_UNAUTHORIZED,
            $previous
        );
    }

    /**
     * (403) Generic forbidden error exception.
     *
     * @param string|null     $message
     * @param string|null     $description
     * @param int|null        $code
     * @param \Throwable|null $previous
     *
     * @return HttpException
     */
    public static function forbidden(
        string $message = null,
        string $description = null,
        int $code = null,
        \Throwable $previous = null
    ): HttpException {
        return static::create(
            $message ? trim($message) : 'Forbidden',
            $description ? trim($description) : '',
            $code ?? StatusCodeInterface::STATUS_FORBIDDEN,
            StatusCodeInterface::STATUS_FORBIDDEN,
            $previous
        );
    }

    /**
     * (404) Generic not found error exception.
     *
     * @param string|null     $message
     * @param string|null     $description
     * @param int|null        $code
     * @param \Throwable|null $previous
     *
     * @return HttpException
     */
    public static function notFound(
        string $message = null,
        string $description = null,
        int $code = null,
        \Throwable $previous = null
    ): HttpException {
        return static::create(
            $message ? trim($message) : 'Not found',
            $description ? trim($description) : '',
            $code ?? StatusCodeInterface::STATUS_NOT_FOUND,
            StatusCodeInterface::STATUS_NOT_FOUND,
            $previous
        );
    }

    /**
     * (405) Generic method not allowed error exception.
     *
     * @param string|null     $message
     * @param string|null     $description
     * @param int|null        $code
     * @param \Throwable|null $previous
     *
     * @return HttpException
     */
    public static function methodNotAllowed(
        string $message = null,
        string $description = null,
        int $code = null,
        \Throwable $previous = null
    ): HttpException {
        return static::create(
            $message ? trim($message) : 'Method not allowed',
            $description ? trim($description) : '',
            $code ?? StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED,
            StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED,
            $previous
        );
    }

    /**
     * (406) Generic not acceptable error exception.
     *
     * @param string|null     $message
     * @param string|null     $description
     * @param int|null        $code
     * @param \Throwable|null $previous
     *
     * @return HttpException
     */
    public static function notAcceptable(
        string $message = null,
        string $description = null,
        int $code = null,
        \Throwable $previous = null
    ): HttpException {
        return static::create(
            $message ? trim($message) : 'Not acceptable',
            $description ? trim($description) : '',
            $code ?? StatusCodeInterface::STATUS_NOT_ACCEPTABLE,
            StatusCodeInterface::STATUS_NOT_ACCEPTABLE,
            $previous
        );
    }

    /**
     * (409) Generic conflict error exception.
     *
     * @param string|null     $message
     * @param string|null     $description
     * @param int|null        $code
     * @param \Throwable|null $previous
     *
     * @return HttpException
     */
    public static function conflict(
        string $message = null,
        string $description = null,
        int $code = null,
        \Throwable $previous = null
    ): HttpException {
        return static::create(
            $message ? trim($message) : 'Conflict',
            $description ? trim($description) : '',
            $code ?? StatusCodeInterface::STATUS_CONFLICT,
            StatusCodeInterface::STATUS_CONFLICT,
            $previous
        );
    }

    /**
     * (410) Generic gone error exception.
     *
     * @param string|null     $message
     * @param string|null     $description
     * @param int|null        $code
     * @param \Throwable|null $previous
     *
     * @return HttpException
     */
    public static function gone(
        string $message = null,
        string $description = null,
        int $code = null,
        \Throwable $previous = null
    ): HttpException {
        return static::create(
            $message ? trim($message) : 'Gone',
            $description ? trim($description) : '',
            $code ?? StatusCodeInterface::STATUS_GONE,
            StatusCodeInterface::STATUS_GONE,
            $previous
        );
    }

    /**
     * (415) Generic unsupported media type error exception.
     *
     * @param string|null     $message
     * @param string|null     $description
     * @param int|null        $code
     * @param \Throwable|null $previous
     *
     * @return HttpException
     */
    public static function unsupportedMediaType(
        string $message = null,
        string $description = null,
        int $code = null,
        \Throwable $previous = null
    ): HttpException {
        return static::create(
            $message ? trim($message) : 'Unsupported media type',
            $description ? trim($description) : '',
            $code ?? StatusCodeInterface::STATUS_UNSUPPORTED_MEDIA_TYPE,
            StatusCodeInterface::STATUS_UNSUPPORTED_MEDIA_TYPE,
            $previous
        );
    }

    /**
     * (422) Generic unprocessable entity error exception.
     *
     * @param string|null     $message
     * @param string|null     $description
     * @param int|null        $code
     * @param \Throwable|null $previous
     *
     * @return HttpException
     */
    public static function unprocessableEntity(
        string $message = null,
        string $description = null,
        int $code = null,
        \Throwable $previous = null
    ): HttpException {
        return static::create(
            $message ? trim($message) : 'Unprocessable entity',
            $description ? trim($description) : '',
            $code ?? StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
            StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
            $previous
        );
    }

    /**
     * (429) Generic too many requests error exception.
     *
     * @param string|null     $message
     * @param string|null     $description
     * @param int|null        $code
     * @param \Throwable|null $previous
     *
     * @return HttpException
     */
    public static function tooManyRequests(
        string $message = null,
        string $description = null,
        int $code = null,
        \Throwable $previous = null
    ): HttpException {
        return static::create(
            $message ? trim($message) : 'Too many requests',
            $description ? trim($description) : '',
            $code ?? StatusCodeInterface::STATUS_TOO_MANY_REQUESTS,
            StatusCodeInterface::STATUS_TOO_MANY_REQUESTS,
            $previous
        );
    }

    /**
     * (500) Generic internal server error exception.
     *
     * @param string|null     $message
     * @param string|null     $description
     * @param int|null        $code
     * @param \Throwable|null $previous
     *
     * @return HttpException
     */
    public static function internalServerError(
        string $message = null,
        string $description = null,
        int $code = null,
        \Throwable $previous = null
    ): HttpException {
        return static::create(
            $message ? trim($message) : 'Internal server error',
            $description ? trim($description) : '',
            $code ?? StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR,
            StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR,
            $previous
        );
    }

    /**
     * (501) Generic not implemented error exception.
     *
     * @param string|null     $message
     * @param string|null     $description
     * @param int|null        $code
     * @param \Throwable|null $previous
     *
     * @return HttpException
     */
    public static function notImplemented(
        string $message = null,
        string $description = null,
        int $code = null,
        \Throwable $previous = null
    ): HttpException {
        return static::create(
            $message ? trim($message) : 'Not implemented',
            $description ? trim($description) : '',
            $code ?? StatusCodeInterface::STATUS_NOT_IMPLEMENTED,
            StatusCodeInterface::STATUS_NOT_IMPLEMENTED,
            $previous
        );
    }

    /**
     * Get new HTTP exception.
     *
     * @param string          $message
     * @param string          $description
     * @param int             $code
     * @param int             $statusCode
     * @param \Throwable|null $previous
     *
     * @return HttpException
     */
    public static function create(
        string $message,
        string $description,
        int $code,
        int $statusCode,
        \Throwable $previous = null
    ): HttpException {
        return new HttpException($message, $description, $code, $statusCode, $previous);
    }
}
