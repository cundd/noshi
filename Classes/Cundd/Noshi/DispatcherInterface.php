<?php
declare(strict_types=1);

namespace Cundd\Noshi;

interface DispatcherInterface
{
    /**
     * Dispatch the given request URI
     *
     * @param string $uri
     * @param string $method
     * @param array  $arguments
     * @return Response
     */
    public function dispatch(string $uri, string $method = 'GET', array $arguments = []): Response;
}
