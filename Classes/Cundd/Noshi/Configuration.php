<?php

declare(strict_types=1);

namespace Cundd\Noshi;

use ArrayAccess;
use Cundd\Noshi\Exception\SecurityException;

/**
 * Configuration container
 *
 * @method string getBasePath()
 * @method string getDataPath()
 * @method string getTemplatePath()
 */
class Configuration implements ArrayAccess
{
    /**
     * @var bool
     */
    protected bool $useVendorDirectory = true;

    /**
     * @var array
     */
    protected array $configuration = [
        'dataPath'     => 'data/',
        'dataSuffix'   => 'md',
        'templatePath' => 'Resources/Private/Templates/',
        'resourcePath' => 'Resources/Public/',

        'metaData' => [
            'title' => 'NoShi',
        ],
    ];

    function __construct(array $configuration = [])
    {
        $this->configuration = array_merge(
            $this->configuration,
            $this->prepareConfigurationArray((array)$configuration)
        );
        $this->useVendorDirectory = file_exists($this->getThemePath());
    }

    /**
     * Prepare the configuration values
     *
     * Append a slash at the end of directory configurations
     *
     * @param array $configuration
     * @return array
     */
    private function prepareConfigurationArray(array $configuration): array
    {
        $pathConfiguration = [
            'dataPath',
            'templatePath',
            'resourcePath',
        ];
        foreach ($pathConfiguration as $key) {
            if (isset($configuration[$key])) {
                $configuration[$key] = rtrim($configuration[$key], '/') . '/';
            }
        }

        return $configuration;
    }

    /**
     * Returns the base URL
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        $baseUrl = $this->_get('baseUrl');
        if (!$baseUrl) {
            $baseUrl = ''
                . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http')
                . '://'
                . $this->getHost()
                . '/';
        }

        $requestBasePath = $this->getRequestBasePath();
        if ($requestBasePath) {
            return $baseUrl . $requestBasePath;
        }

        return $baseUrl;
    }

    /**
     * Returns the sanitized hostname
     *
     * @return string
     * @throws Exception\SecurityException if the host could not be detected
     */
    public function getHost(): string
    {
        $host = '';

        if (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] !== '0.0.0.0') {
            $host = $_SERVER['SERVER_NAME'];
            if (!$this->validateHost($host)) {
                $host = '';
            }

            // Add the port
            if ($host && isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT']) {
                $port = $_SERVER['SERVER_PORT'];
                if (ctype_digit($port)) {
                    $host .= ':' . $port;
                }
            }
        }

        if (!$host && isset($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
            if (!$this->validateHost($host)) {
                $host = '';
            }
        }
        if (!$host) {
            throw new SecurityException('Host could not be detected', 1394796366);
        }

        return $host;
    }

    /**
     * Returns if the given host is valid
     *
     * @param string $host
     * @return boolean
     */
    protected function validateHost(string $host): bool
    {
        // Remove any dash ('-'), dot ('.') and colon (':', allowed because of the port)
        return ctype_alnum(str_replace(['-', '.', ':'], '', $host));
    }

    /**
     * Returns the path to the configured theme
     *
     * @return string
     */
    public function getThemePath(): string
    {
        return $this->get('basePath') .
            ($this->useVendorDirectory ? 'vendor/' . $this->get('theme') : '')
            . '/';
    }

    /**
     * Returns the URI of the path to the configured theme
     *
     * @return string
     */
    public function getThemeUri(): string
    {
        return $this->getBaseUrl() .
            ($this->useVendorDirectory ? 'vendor/' . $this->get('theme') : '')
            . '/';
    }

    /**
     * Returns the URI of the resource directory
     *
     * @return string
     */
    public function getResourceDirectoryUri(): string
    {
        return $this->getThemeUri() . $this->get('resourcePath');
    }

    /**
     * Returns the name of the subdirectory the project is installed in
     *
     * @return string
     */
    public function getRequestBasePath(): string
    {
        $trimmedBasePath = trim((string)$this->_get('requestBasePath'), '/');

        if (!$trimmedBasePath) {
            return '/';
        }

        return '/' . $trimmedBasePath . '/';
    }

    /**
     * Returns if the website is in Development Mode
     *
     * @return bool
     */
    public function isDevelopmentMode(): bool
    {
        return (bool)$this->get('developmentMode');
    }

    /**
     * Returns the configuration (without checking for an accessor method)
     *
     * @param string $key
     * @return mixed
     */
    protected function _get(string $key): mixed
    {
        if (isset($this->configuration[$key])) {
            return $this->configuration[$key];
        }

        return null;
    }

    /**
     * Returns the configuration
     *
     * If an accessor method (i.e.: "getMyKey") is available it will be called
     *
     * @param string $key
     * @return mixed
     */
    public function get(string $key): mixed
    {
        $accessorMethod = 'get' . ucfirst($key);
        if (method_exists($this, $accessorMethod)) {
            return $this->$accessorMethod();
        }

        return $this->_get($key);
    }

    /**
     * Set the configuration
     *
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function set(string $key, mixed $value): self
    {
        $this->configuration[$key] = $value;

        return $this;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->configuration[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset): void
    {
        $this->set($offset, null);
    }

    function __call($name, $arguments)
    {
        if (str_starts_with($name, 'get')) {
            return $this->get(lcfirst(substr($name, 3)));
        }

        return null;
    }
}
