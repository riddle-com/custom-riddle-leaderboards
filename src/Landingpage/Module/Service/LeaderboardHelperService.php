<?php

namespace Riddle\Landingpage\Module\Service;

use Riddle\Landingpage\Module\LeaderboardModule;
use Riddle\Landingpage\RiddleData;

class LeaderboardHelperService
{

    private $module;
    private $lastLeadDisplayed;

    public function __construct(LeaderboardModule $module) 
    {
        $this->module = $module;
        $this->lastLeadDisplayed = 0; // helper variable to ensure that no leads are displayed twice
    }

    /**
     * Returns the placement the user is on and finds the user by a lead key.
     * 
     * @param $data (RiddleData) a RiddleData object
     * @return (int|boolean) the placement the user is on. e.g. returns 1 => first place; returns false if the user is not on the leaderboard
     */
    public function getPlacementByData(RiddleData $data)
    {
        $leadKeyValue = $this->getLeadKeyValue($data);
        $keyIndex = $this->module->getStoreService()->getKeyIndex($leadKeyValue); // look up key / hash table

        if (!$leadKeyValue || !$keyIndex) {
            return false;
        }

        return $keyIndex;
    }

    public function getLeadKeyValue(RiddleData $data)
    {
        $leadKey = $this->getLeadKey();

        if (!$data->getLead() || !isset($data->getLead()->$leadKey)) {
            return false;
        }

        return $data->getLead()->$leadKey->value;
    }

    public function getLeadKeyValueInArray(array $data)
    {
        $leadKey = $this->getLeadKey();

        if (!isset($data['lead2']) || !isset($data['lead2'][$leadKey])) {
            return false;
        }

        return $data['lead2'][$leadKey]['value'];
    }

    public function getLeadKey()
    {
        return $this->module->getApp()->getConfig()->getProperty('leadKey');
    }

    public function setLastLeadDisplayed(int $lastLeadDisplayed)
    {
        $this->lastLeadDisplayed = $lastLeadDisplayed;
    }

    public function getLastLeadDisplayed() :int
    {
        return $this->lastLeadDisplayed;
    }

    /**
     * Returns whether every lead is displayed on the leaderboard already.
     */
    public function everyLeadisDisplayed()
    {
        return $this->lastleadDisplayed == count($this->module->getStoreService()->getEntries());
    }

}