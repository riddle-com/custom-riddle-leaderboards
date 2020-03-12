<?php

/**
 * @since 1.0
 * 
 * This block renders the last leaderboard lead.
 * 
 * @param $args (array) the following args exist:
 *  - template: the html template this block uses to render.
 */

namespace Riddle\Landingpage\Module\Block;

use Riddle\Tools\RiddleTools;

class LeaderboardLastLeadBlock extends LeaderboardLeadsBlock
{

    public function render(array $args)
    {
        // render the last lead only if it's not already displayed
        $args = array_merge($args, [
            'onlyOnce' => true,
            'range' => 'last',
        ]);

        // don't display the "..." table row if the leaderboard does not have entries in between (to prevent 5 ... 6)
        if (($this->module->getStoreService()->getTotalEntries() - 1) === $this->module->getHelperService()->getLastLeadDisplayed()) {
            unset($args['templatePrefix']);
        }
        
        return parent::render($args);
    }
    
}