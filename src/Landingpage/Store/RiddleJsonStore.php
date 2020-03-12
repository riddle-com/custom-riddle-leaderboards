<?php

/**
 * @since 1.0
 */

namespace Riddle\Landingpage\Store;

use Riddle\Tools\RiddleTools;

class RiddleJsonStore extends RiddleStore
{

    public function load()
    {
        $leadsFilePath = $this->_getLeadsFilePath();

        if (!file_exists($leadsFilePath)) {
            return false;
        }

        $this->leads = \json_decode(file_get_contents($leadsFilePath), true);
    }

    public function store()
    {
        RiddleTools::saveFile($this->_getLeadsFilePath(), json_encode($this->leads));
    }

    private function _getLeadsFilePath()
    {
        $appDir = $this->app->getConfig()->getProperty('dataPath');

        return $appDir . '/leads-' . $this->app->getRiddleId() . '.json';
    }

}