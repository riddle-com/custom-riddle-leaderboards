<?php

/**
 * @since 1.0
 * 
 * This block can render a specific range of leaderboard leads.
 * 
 * @param $args (array) the following arguments exist:
 *  - range: 
 *      - can be a number: e.g. 5 => only displays the 5th entry
 *      - can be an array: e.g. [1, 5] => displays the leaderboard entries from 1 to 5
 *      - can be a string: 'all', 'last'
 */

namespace Riddle\Landingpage\Module\Block;

use Riddle\Tools\RiddleTools;

class LeaderboardLeadsBlock extends ModuleBlock
{
    private $args;

    public function render(array $args)
    {
        $this->args = $args;

        if (!isset($args['range']) || !isset($args['template'])) {
            throw new \InvalidArgumentException('required arguments: range & template');
        }

        $range = $this->_getRange($args['range'], $args);

        // lead range is already rendered and shouldn't be rendered twice
        if ($this->_renderOnlyOnce($args) && $this->_leadRangeIsAlreadyDisplayed($range)) {
            return '';
        }

        $html = '';

        if ($range['start'] > $this->module->getHelperService()->getLastLeadDisplayed() && $this->_renderOnlyOnce($args)) {
            $this->_appendPrefix($html, $args);
        }

        $template = $args['template'];
        $spotTemplate = isset($args['spotTemplate']) ? $args['spotTemplate'] : $template;
        $html .= $this->_renderTableRows($range, $template, $spotTemplate);
        $this->_appendSuffix($html, $args);

        return $html;
    }

    private function _renderTableRows($range, $htmlTemplate, $spotTemplate)
    {
        $html = '';
        $i = $range['start'];
        $arrayLength = $range['end'] - $range['start'] + 1;
        $leaderboardLeads = $this->_getLeaderboardLeads();
        $leads = array_slice($leaderboardLeads, $i, $arrayLength);

        $this->module->getHelperService()->setLastLeadDisplayed($i + $arrayLength);

        foreach ($leads as $index => $data) {
            // skip rendering because this lead entry is already displayed
            if ($index > $this->module->getHelperService()->getLastLeadDisplayed() && $this->_renderOnlyOnce($args)) {
                continue;
            }

            $template = $this->_isCurrentLead($data) ? $spotTemplate : $htmlTemplate;
            $htmlRow = $this->_renderTableRow($i, $data['key'], $template);
            $html .= $htmlRow;

            $i++;
        }

        return trim($html);
    }

    private function _renderTableRow($i, $leadKey, $htmlTemplate) 
    {
        $lead = $this->module->getApp()->getStore()->getLead($leadKey);

        if (!$lead) {
            return null;
        }

        $lead['index'] = $i + 1; // usually, lists start with 1 - not with 0 fellow programmers :)
        $matches = RiddleTools::getEverythingInTags($htmlTemplate);

        if (empty($matches)) {
            return $htmlTemplate;
        }

        foreach ($matches[1] as $match) {
            $matchParts = explode(':', $match, 2);
            $dataValue = RiddleTools::getArrayElementFromInnerHtml(trim($matchParts[0]), $lead);

            if (isset($matchParts[1]) ) {
                $dataValue = $this->_filterValue($matchParts[1], $dataValue);
            }

            $htmlTemplate = str_replace('{' . $match . '}', $dataValue, $htmlTemplate);
        }

        return $htmlTemplate;
    }

    /**
     * Returns whether the current lead the leaderboard has received is equals the lead in the current iteration
     */
    private function _isCurrentLead(array $data)
    {
        $currentData = $this->_getData();

        if (!$currentData) {
            return false;
        }

        return $data['key'] === $currentData->getLead()->Email->value;
    }

    protected function _getRange($range) 
    {
        /**
         * Example: 'range' => [1, 10] => leaderboard leads from 1 - 10
         */
        if (is_array($range) && count($range) === 2) {
            $range =  [
                'start' => $range[0],
                'end' => $range[1],
            ];
        }

        /**
         * Example: 'range' => 2 => only display the second leaderboard lead
         */
        if (is_numeric($range) && $range > 0 && $range < $this->getLeaderboardLength()) {
            $range = [
                'start' => $range,
                'end' => $range,
            ];
        }

        /**
         * Example: 'range' => 'all' => display all the leaderboard leads.
         */
        if ($range === 'all') {
            $range = [
                'start' => 1,
                'end' => count($this->_getLeaderboardLeads()),
            ];
        }

        /**
         * Example: 'range' => 'last' => displays only the last leaderboard lead
         */
        if ($range === 'last') {
            $range = [
                'start' => count($this->_getLeaderboardLeads()),
                'end' => count($this->_getLeaderboardLeads()),
            ];
        }

        if (!isset($range)) {
            throw new \InvalidArgumentException(
                'Your range doesn\'t look right - here\'s how the range parameter could look like: 
                can be a number: e.g. 5 => only displays the 5th entry; 
                can be an array: e.g. [1, 5] => displays the leaderboard entries from 1 to 5; 
                can be a string: "all", "last"'
            );
        }

        return [
            'start' => $range['start'] - 1,
            'end' => $range['end'] - 1,
        ];
    }

    protected function _filterValue($filter, $value) 
    {
        $filter = strtolower(trim($filter));

        if ($filter === 'encrypted') {
            return $this->_encryptStringWithAsterisks($value);
        }

        if ($filter === 'encryptedemail') {
            return $this->_encryptEmail($value);
        }

        throw new \InvalidArgumentException('Unknown filter used: ' . $filter . ' available filters: encrypted, encryptedemail');
    }

    protected function _getLeaderboardLeads()
    {
        return $this->module->getStoreService()->getEntries();
    }

    private function _appendPrefix(string &$html, array $args) 
    {
        if (isset($args['templatePrefix'])) {
            $html = $args['templatePrefix'] . $html;
        }

        return $html;
    }

    private function _appendSuffix(string &$html, array $args) 
    {
        if (isset($args['templateSuffix']) && !$this->module->getHelperService()->everyLeadisDisplayed()) {
            $html .= $args['templateSuffix'];
        }

        return $html;
    }

    private function _leadRangeIsAlreadyDisplayed(array $range) 
    {
        return $range['end'] + 1 <= $this->module->getHelperService()->getLastLeadDisplayed();
    }

    private function _insertEncryptedEmail(&$lead) 
    {
        $leadKeyValue = $this->module->getHelperService()->getLeadKeyValueInArray($lead);
        $encryptedMail = 'could not find the lead email.';

        if ($leadKeyValue) {
            $lead['lead2'][$this->module->getHelperService()->getLeadKey()]['value'] = $this->_encryptEmail($leadKeyValue);
        }
    }

    private function _renderOnlyOnce(array $args)
    {
        return isset($args['onlyOnce']) && $args['onlyOnce'];
    }

    private function _encryptEmail(string $email) 
    {
        $emailParts = explode('@', $email);

        if (count($emailParts) !== 2) {
            return $email;
        }

        $emailName = $this->_encryptStringWithAsterisks($emailParts[0]);
        $domainParts = explode('.', $emailParts[1], 2);
        $domainName = $this->_encryptStringWithAsterisks($domainParts[0]);

        return $emailName . '@' . $domainName . '.' . $domainParts[1];
    }

    private function _encryptStringWithAsterisks(string $string) 
    {
        if (strlen($string) <= 2) {
            return str_repeat('*', strlen($string));
        }

        $firstLetter = $string[0];
        $lastLetter = $string[strlen($string) - 1];
        $asterisks = str_repeat('*', strlen($string) - 2);

        return $firstLetter . $asterisks . $lastLetter;
    }

}