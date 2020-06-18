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

        if ($this->module->getApp()->getLeaderboardHandler()->isPreview()) {
            $this->_loadLeaderboardLeads();
            $this->refresh();
        }
    }

    /**
     * Core method of this service.
     * Processes the lead (inserts it / updates it) and eventually refreshes the leads file.
     * 
     * @param bool $save pass false if the file shouldn't be saved (e.g. for test purposes)
     * @return bool returns whether the lead was stored on the leaderboard
     */
    public function processAndStore(RiddleData $data, bool $save = true)
    {
        if (!$data->getLead()) {
            return false;
        }
        
        $this->_loadLeaderboardLeads();
        $onLeaderboard = $this->_checkAndInsertIntoLeaderboard($data);

        if ($onLeaderboard) {
            $this->refresh();

            if ($save) {
                $this->_saveLeaderboardsFile();
            }
        }

        return $onLeaderboard;
    }

    /**
     * Refreshes the leadeboard leads by sorting and refreshing the key table.
     */
    public function refresh()
    {
        $this->_sortLeaderboard();
        $this->_refreshKeyTable();
    }

    /**
     * Sort the multidimensional leaderboard.
     */
    private function _sortLeaderboard()
    {
        if ([] === $this->leads) {
            return true;
        }
        
        $availableModes = ['percentage', 'sums', 'timeS', 'timeP'];
        $mode = $this->module->getMode();

        if (!in_array($mode, $availableModes)) {
            $mode = 'percentage'; // set it back to the default
        }

        // prepare arrays for the sort

        if ('percentage' === $mode || 'timeP' === $mode) {
            $percentages = [];

            foreach ($this->leads['entries'] as $index => $data) {
                $percentages[$index] = $data['percentage'];
            }
        }

        if ('sums' === $mode || 'timeS' === $mode) {
            $sums = [];
            
            foreach ($this->leads['entries'] as $index => $data) {
                $sums[$index] = isset($data['sumScoreNumber']) ? $data['sumScoreNumber'] : 0;
            }
        }

        if ('timeS' === $mode || 'timeP' === $mode) {
            $times = [];
            
            foreach ($this->leads['entries'] as $index => $data) {
                $times[$index] = isset($data['trunk']['timeTaken']) ? $data['trunk']['timeTaken'] : 0;
            }
        }

        switch ($mode) {
            case 'timeP':
                array_multisort($percentages, SORT_DESC, $times, SORT_ASC, $this->leads['entries']);
                break;
            case 'sums':
                array_multisort($sums, SORT_DESC, $this->leads['entries']);
                break;
            case 'timeS':
                array_multisort($sums, SORT_DESC, $times, SORT_ASC, $this->leads['entries']);
                break;
            case 'percentage':
                array_multisort($percentages, SORT_DESC, $this->leads['entries']);
                break;
        }
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
        if (!isset($this->leads['entries'])) {
            return true;
        }

        $keyTable = [];

        foreach ($this->leads['entries'] as $i => $entry) {
            $keyTable[$entry['key']] = $i;
        }

        $this->leads['keyTable'] = $keyTable;
    }

    /**
     * @return bool if the lead could be added to the leaderboard
     */
    private function _checkAndInsertIntoLeaderboard(RiddleData $data)
    {
        if ($this->getTotalEntries() >= $this->module->getLeaderboardLength()) {
            return false;
        }

        return null !== $this->_addData($data);
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
            return null;
        }

        $index = $this->getKeyIndex($leadKeyValue);

        if (false === $index) { // nothing was found.
            return null;
        }

        return $this->leads['entries'][$index];
    }

    /**
     * Inserts a fresh set of data to the leaderboard (if the lead key does not exist already)
     * 
     * @return array the complete leaderboard entry
     */
    private function _addData(RiddleData $data)
    {
        if (!$this->_isOnLeaderboard($data)) {
            $leadKeyValue = $this->module->getHelperService()->getLeadKeyValue($data);
            $entryCount = $this->getTotalEntries();
    
            // add the bare minimum and add more data later via _updateData()
            $this->leads['entries'][] = [
                'key' => $leadKeyValue,
                'createdAt' => RiddleTools::now(),
                'id' => \uniqid(),
            ];

            // to find a lead entry super fast - key table!
            $this->leads['keyTable'][$leadKeyValue] = $entryCount;
        }

        return $this->_updateData($data); // add more data to the entry
    }

    /**
     * Updates scoreNumber and adds a date to the array to keep track of how a user behaves and to spot potentital cheaters.
     * 
     * @return array the complete leaderboard entry
     */
    private function _updateData(RiddleData $data)
    {
        $leadKeyValue = $this->module->getHelperService()->getLeadKeyValue($data);
        $index = $this->getKeyIndex($leadKeyValue);
        $entry = $this->getEntry($index);
        $latestScoreNumber = isset($entry['latestScoreNumber']) ? $entry['latestScoreNumber'] : 0;
        $resultScoreNumber = isset($data->getResultData()->scoreNumber) ? $data->getResultData()->scoreNumber : 0;

        $this->leads['entries'][$index]['percentage'] = round($data->getResultData()->scorePercentage, 2); // only save 2 digits after the dot
        $this->leads['entries'][$index]['updatedAt'] = RiddleTools::now();
        $this->leads['entries'][$index]['sumScoreNumber'] = $latestScoreNumber + $resultScoreNumber; // to make it possible to switch from one mode to another
        $this->leads['entries'][$index]['latestScoreNumber'] = $resultScoreNumber;
        $this->leads['entries'][$index]['dates'][] = RiddleTools::now();

        foreach ($data->getLeadFields() as $field) {
            $this->leads['entries'][$index]['lead'][$field] = $data->getLead()->$field->value;
        }

        $this->leads['entries'][$index]['trunk'] = $data->getJsonData(); // to avoid a second file

        return $this->leads['entries'][$index];
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

    private function _isOnLeaderboard(RiddleData $data)
    {
        $leadKeyValue = $this->module->getHelperService()->getLeadKeyValue($data);

        if (!$leadKeyValue) { // the data contains no lead data and therefore no lead key value exists
            return null;
        }

        return false !== $this->getKeyIndex($leadKeyValue);
    }

    private function _saveLeaderboardsFile()
    {
        RiddleTools::saveFile($this->_getLeaderboardLeadsPath(), json_encode($this->leads));
    }

}