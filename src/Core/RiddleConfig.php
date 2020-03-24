<?php

/**
 * @since 1.0
 * 
 * This class manages the riddle config.
 */

namespace Riddle\Core;

use Riddle\Exception\BadConfigException;
use Riddle\Exception\FileNotFoundException;

class RiddleConfig
{

    private $app;
    private $properties;

    // makes it a lot easier for normal users: no need to create a config.
    private $defaultProperties;

    public function __construct()
    {
        $this->properties = [];
        $this->_loadDefaultConfig();
    }

    /**
     * Returns a property with the given $propertyName
     * 
     * @param $propertyName (mixed)
     * @return (mixed) property object
     */
    public function getProperty($propertyName)
    {
        if (!isset($this->properties[$propertyName])) {
            return $this->defaultProperties[$propertyName];
        }

        return $this->properties[$propertyName];
    }

    /**
     * Returns all the properties of a config.
     * 
     * @return (array) returns an empty array if the (sub)config doesn't exist
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Adds the properties of a config file to this config
     * 
     * @param $configPath (string) the path of the config file
     * @throws Riddle\Exception\FileNotFoundException if the subconfig file doesn't exist.
     * @throws Riddle\Exception\BadConfigException if the config is faulty (either $config is not set or $config is not an array)
     * @return (array) all the properties of the loaded config
     */
    public function addConfigFile(string $configPath)
    {
        if (!file_exists($configPath)) {
            throw new FileNotFoundException('The config you wanted to add can\'t be found (path: ' . $configPath . ').');
        }
        
        require $configPath;

        if (!isset($config) || !is_array($config)) {
            throw new BadConfigException('The (sub)config is faulty - make sure that the variable $config is set and that the $config variable is an array.');
        }

        $this->properties = $config;

        return $config;
    }

    /**
     * Adds properties to the config.
     */
    public function addProperties(array $properties)
    {
        $this->properties = array_merge($this->properties, $properties);
    }

    private function _loadDefaultConfig()
    {
        require SRC_DIR . '/Core/RiddleDefaultConfig.php';

        $this->defaultProperties = $config;
    }

}