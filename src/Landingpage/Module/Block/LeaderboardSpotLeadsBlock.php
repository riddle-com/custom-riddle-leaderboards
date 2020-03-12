<?php

/**
 * @since 1.0
 * 
 * This block highlights the lead range the user is in.
 * 
 * @param $args (array) the following arguments exist:
 *  - range: 
 *      - can be one number: e.g. 1 => renders one lead before and after the user.
 *      - can be two numbers: e.g. [1, 2] => renders one lead before the user and 2 after the user
 */

namespace Riddle\Landingpage\Module\Block;

class LeaderboardSpotLeadsBlock extends LeaderboardLeadsBlock
{

    private $leadKeyValue;
    private $keyIndex;
    
    public function render(array $args) 
    {
        // skip this block if the user hasn't filled in the lead form / if no data is available
        if (!$this->module->getApp()->hasData()) {
            return "";
        }

        $this->leadKeyValue = $this->module->getHelperService()->getLeadKeyValue($this->_getData());
        $this->keyIndex = $this->module->getStoreService()->getKeyIndex($this->leadKeyValue);
        
        return parent::render(array_merge($args, [
            'range' => $args['range'],
            'onlyOnce' => true,
        ]));
    }

    /**
     * @Override
     */
    protected function _getRange($range)
    {
        if (is_array($range) && count($range) === 2) {
            $beforeStart = $this->keyIndex - $range[0];
            $afterEnd = $this->keyIndex + $range[1];
        } else if (is_numeric($range)) {
            $beforeStart = $this->keyIndex - $range;
            $afterEnd = $this->keyIndex + $range;
        } else {
            throw new \InvalidArgumentException('Faulty range format: Use either an array ([2, 2]) or just a normal number (2)');
        }

        $beforeStart = max($this->module->getHelperService()->getLastLeadDisplayed(), $beforeStart);

        return [
            'start' => $beforeStart,
            'end' => $afterEnd,
        ];
    }

}