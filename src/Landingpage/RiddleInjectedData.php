<?php

namespace Riddle\Landingpage;

/**
 * This data gets injected to all the views & templates and gives the developer the freedom
 * of pasing any data to these views/templates.
 * 
 * This gets used in our WP plugin. Have a look at the source to see how you can implement it yourself!
 */

class RiddleInjectedData
{

    private $data;
    private $defaultData;

    public function __construct($data, $defaultData = null)
    {
        $this->data = $data;
        $this->defaultData = $defaultData;
    }

    public function getValue($key, $default = null, $fallbackToDefault = true) 
    {
        if (!isset($this->data[$key])) {
            return $fallbackToDefault ? $this->_getDefaultValue($key) : $default;
        }

        return $this->data[$key];
    }

    private function _getDefaultValue($key)
    {
        if (!isset($this->defaultData[$key])) {
            return null;
        }

        return $this->defaultData[$key];
    }

}