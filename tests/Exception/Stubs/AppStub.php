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

namespace Jgut\Slim\Exception\Tests\Stubs;

use Jgut\Slim\Exception\HttpExceptionAwareTrait;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class AppStub
{
    use HttpExceptionAwareTrait {
        getLastError as originalGetLastError;
    }

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $lastError;

    /**
     * AppStub constructor.
     *
     * @param ContainerInterface $container
     * @param array              $lastError
     */
    public function __construct(ContainerInterface $container, array $lastError = [])
    {
        $this->container = $container;
        $this->lastError = $lastError;

        $this->registerPhpErrorHandling();
    }

    /**
     * {@inheritdoc}
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * {@inheritdoc}
     */
    protected function getLastError(): array
    {
        if (\count($this->lastError)) {
            return $this->lastError;
        }

        return $this->originalGetLastError();
    }

    /**
     * {@inheritdoc}
     */
    public function respond(ResponseInterface $response)
    {
        echo $response->getBody();
    }
}
