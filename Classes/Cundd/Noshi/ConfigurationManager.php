<?php

namespace Cundd\Noshi;


class ConfigurationManager
{
    /**
     * @var Configuration
     */
    static protected $sharedConfiguration;

    /**
     * Initializes the shared configuration
     *
     * @param string $basePath
     * @return Configuration
     */
    static public function initializeConfiguration($basePath)
    {
        // Read the configurations file
        $configuration = [];
        $configurationFile = $basePath . 'Configurations/Configuration.json';
        if (file_exists($configurationFile) && is_readable($configurationFile)) {
            $configuration = json_decode(file_get_contents($configurationFile), true);
        }

        self::$sharedConfiguration = new Configuration($configuration);
        self::$sharedConfiguration->set('basePath', $basePath);

        return self::$sharedConfiguration;
    }

    /**
     * Returns the shared configuration
     *
     * @return Configuration
     */
    static public function getConfiguration()
    {
        return self::$sharedConfiguration;
    }
} 