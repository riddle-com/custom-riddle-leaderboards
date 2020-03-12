<?php

/**
 * @since 1.0
 */

namespace Riddle\Landingpage\Store;

use Riddle\Core\RiddleApp;
use Riddle\Exception\BadConfigException;
use Riddle\Landingpage\RiddleData;

abstract class RiddleStore
{
    
    protected $app;
    protected $leads;

    public function __construct(RiddleApp $app)
    {
        $this->app = $app;
    }

    abstract function load();
    abstract function store(); // save function

    public function isLoaded()
    {
        return $this->leads !== null;
    }

    public function addLead(RiddleData $data, RiddleApp $app)
    {
        $lead = $data->getLead();

        if (!$lead) {
            return false;
        }

        $leadKey = $app->getConfig()->getProperty('leadKey');

        if (!isset($lead->$leadKey)) {
            throw new BadConfigException('We did not receive the right data to save this lead. the lead key you set (' . $leadKey . ') was not received by this webhook.');
        }

        $leadKeyValue = $lead->$leadKey->value;
        $data = $data->getJsonData();
        $data['createdAt'] = time();
        $this->leads[$leadKeyValue] = $data;

        return $data;
    }

    public function removeLead($key)
    {
        if (!isset($this->leads[$key])) {
            return false;
        }
        
        unset($this->leads[$key]);
    }

    public function getLead($leadKey) 
    {
        if (!isset($this->leads[$leadKey])) {
            return false;
        }
        
        return $this->leads[$leadKey];
    }

    public function getLeads()
    {
        return $this->leads;
    }

}

