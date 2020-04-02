<?php

namespace Riddle\Landingpage\Module\Service;

use Riddle\Exception\BadConfigException;
use Riddle\Tools\RiddleTools;
use Riddle\Landingpage\Module\LeaderboardModule;
use Riddle\Landingpage\RiddleData;

class LeaderboardStoreService
{

    private $module;

    private $leads;

    public function __construct(LeaderboardModule $module)
    {
        $this->module = $module;
        $this->leads = [];
    }

    /**
     * This methods stores the lead into the data json file.
     * Checks:
     *  - already on the leaderboard? => is the new lead result better than the previous one?
     */
    public function processAndStore(RiddleData $data)
    {
        if (!$data->getLead()) {
            return false;
        }
        
        $this->_loadLeaderboardLeads();

        if(!$this->_checkForDuplicates($data)) {
            return false;
        }

        $onLeaderboard = $this->_checkAndInsertIntoLeaderboard($data);

        if ($onLeaderboard === true) {
            $this->_sortLeaderboard();
            $this->_refreshKeyTable();
            $this->_saveLeaderboardsFile();
        }

        return $onLeaderboard;
    }

    /**
     * Sort the multidimensional leaderboard.
     */
    private function _sortLeaderboard()
    {
        $percentages = [];
        $i = 0;

        foreach ($this->leads['entries'] as $index => $data) {
            $percentages[$index] = $data['percentage'];
            $i++;
        }

        array_multisort($percentages, SORT_DESC, $this->leads['entries']);
    }

    /**
     * Refreshes the key table.
     * This key table enables the leaderboard to look up every lead as quickly as possible.
     * 
     * Reason: Because isset, array_key_exists, ... is way faster than array_search it's easier to just
     * create a key/hash table which contains the key indexes of the associated entries.
     */
    private function _refreshKeyTable()
    {
        $keyTable = [];
        $i = 0;

        foreach ($this->leads['entries'] as $entry) {
            $keyTable[$entry['key']] = $i;
            $i++;
        }

        $this->leads['keyTable'] = $keyTable;
    }

    private function _checkAndInsertIntoLeaderboard(RiddleData $data)
    {
        if (count($this->getEntries()) < $this->module->getLeaderboardLength()) {
            $this->_addDataToLeaderboard($data);
            
            return true;
        }

        $leadPercentage  = $data->getResultData()->scorePercentage;

        foreach ($this->leads['entries'] as $i => $entry) {
            if ($entry['percentage'] < $leadPercentage) {
                unset($this->leads['entries'][$i]);
                $this->_addDataToLeaderboard($data);

                return true;
            }
        }
    }

    private function _checkForDuplicates(RiddleData $data)
    {
        $leaderboardLead = $this->getLeaderboardLeadByKey($data);
        if (!$leaderboardLead) { // the user isn't in the leaderboards yet
            return true;
        }

        // return the old result because this one is better
        if ($data->getResultData()->scorePercentage > $leaderboardLead['percentage']) {
            $this->_deleteLeaderboardLead($data);
            
            return true;
        }

        return false; // old result is better - no need to save the new one
    }

    /**
     * Gets a leaderboard entry by a RiddleData object.
     * Procedure: Looks key up in the key table and if the key exists it returns an index of the entries index.
     * This is particularly faster because array_key_exists() is way faster than array_search() e.g.
     * 
     * @param $data The riddle data which should be looked for
     * @return (array|boolean) returns false if the entry doesn't exist
     */
    public function getLeaderboardLeadByKey(RiddleData $data)
    {
        $leadKeyValue = $this->module->getHelperService()->getLeadKeyValue($data);

        if (!$leadKeyValue) { // the data contains no lead data and therefore no lead key value exists
            return false;
        }

        $index = $this->getKeyIndex($leadKeyValue);

        if (!$index) { // nothing was found.
            return false;
        }

        return $this->leads['entries'][$index];
    }

    private function _addDataToLeaderboard(RiddleData $data)
    {
        $leadKeyValue = $this->module->getHelperService()->getLeadKeyValue($data);
        $entryCount = $this->getTotalEntries();

        $this->leads['entries'][] = [
            'key' => $leadKeyValue,

            // gets only saved to sort the leaderboard as fast as possible
            'percentage' => $data->getResultData()->scorePercentage,
            
            // first placement - keep track on how many places a user has dropped
            'placement' => $entryCount + 1,
            'createdAt' => time(),
        ];

        // to find a lead entry super fast
        $this->leads['keyTable'][$leadKeyValue] = $entryCount - 1; 
    }

    private function _deleteLeaderboardLead(RiddleData $data)
    {
        $leadKeyValue = $this->module->getHelperService()->getLeadKeyValue($data);
        $keyIndex = $this->getKeyIndex($leadKeyValue);

        if (!$keyIndex) {
            return false;
        }

        unset($this->leads['entries'][$keyIndex]);
    }

    private function _loadLeaderboardLeads()
    {
        // the leads have already been loaded
        if (!empty($this->leads)) {
            return $this->leads;
        }

        $handler = $this->module->getApp()->getLeaderboardHandler();

        if ($handler && is_array($handler->getEntries())) {
            $this->leads = $handler->getEntries();

            return $this->leads;
        }

        $leaderboardLeadsPath = $this->_getLeaderboardLeadsPath();

        if (!file_exists($leaderboardLeadsPath)) {
            return false;
        }

        $this->leads = json_decode(file_get_contents($leaderboardLeadsPath), true);

        if (!$this->leads) {
            return [];
        }

        return $this->leads;
    }

    public function getLeaderboardLeads()
    {
        return $this->_loadLeaderboardLeads();
    }

    public function getEntry(int $keyIndex)
    {
        $this->_loadLeaderboardLeads();

        return isset($this->leads['entries'][$keyIndex])
            ? $this->leads['entries'][$keyIndex]
            : null;
    }

    public function getEntries()
    {
        $this->_loadLeaderboardLeads();

        return isset($this->leads['entries'])
            ? $this->leads['entries']
            : [];
    }

    public function getTotalEntries()
    {
        return count($this->getEntries());
    }

    public function getKeyTable()
    {
        $this->_loadLeaderboardLeads();

        return isset($this->leads['keyTable'])
            ? $this->leads['keyTable']
            : [];
    }

    public function getKeyIndex($leadKey) 
    {
        if (!isset($this->leads['keyTable'][$leadKey])) {
            return false;
        }

        return $this->leads['keyTable'][$leadKey];
    }

    private function _getLeaderboardLeadsPath()
    {
        $appDir = $this->module->getApp()->getConfig()->getProperty('dataPath');

        return $appDir . '/leaderboard-leads-' . $this->module->getApp()->getRiddleId() . '.json';
    }

    private function _saveLeaderboardsFile()
    {
        RiddleTools::saveFile($this->_getLeaderboardLeadsPath(), json_encode($this->leads));
    }

}