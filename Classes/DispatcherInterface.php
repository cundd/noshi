<?php

declare(strict_types=1);

namespace Cundd\Noshi;

interface DispatcherInterface
{
    /**
     * Dispatch the given request URI
     */
    public function dispatch(string $uri, string $method = 'GET', array $arguments = []): Response;
}
