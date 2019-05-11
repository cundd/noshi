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
     * @param string $dataPath
     * @return Configuration
     */
    static public function initializeConfiguration(string $basePath, ?string $dataPath)
    {
        // Read the configurations file
        $configuration = [];
        $configurationFile = $basePath . 'Configurations/Configuration.json';

        if (file_exists($configurationFile) && is_readable($configurationFile)) {
            $configuration = json_decode(file_get_contents($configurationFile), true);
        }

        $configuration['basePath'] = $basePath;
        if (null !== $dataPath) {
            $configuration['dataPath'] = $dataPath;
        }
        self::$sharedConfiguration = new Configuration($configuration);

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
