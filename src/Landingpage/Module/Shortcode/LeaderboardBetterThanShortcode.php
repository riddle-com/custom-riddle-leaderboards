<?php

namespace Riddle\Landingpage\Module\Shortcode;

class LeaderboardBetterThanShortcode extends ModuleShortcode
{

    private static $NAME = 'better-than';
    private static $TEMPLATE = "You're better than %%PERCENTAGE%%% of all the quiz takers. Congratulations!";

    public function __construct($module) 
    {
        parent::__construct(self::$NAME, $module);
    }

    public function render(array $args)
    {
        if (!$this->module->getApp()->hasData()) {
            return "";
        }

        $placement = $this->module->getHelperService()->getPlacementByData($this->module->getApp()->getData());

        if ($placement === 1) {
            return ''; // makes no sense to display this block if the user is on the first place.
        }

        $totalEntries = $this->module->getStoreService()->getTotalEntries();
        $betterThan = 100 - floor($placement / ($totalEntries / 100));

        if ($betterThan < $this->_getMinValue($args) || $totalEntries === $placement) {
            return ''; // shouldn't be displayed because the quiz taker didn't reach the minimum value
        }

        return $this->_getTemplate($args, $betterThan);
    }

    private function _getMinValue(array $args)
    {
        return isset($args['min']) ? $args['min'] : 0;
    }

    private function _getTemplate(array $args, $percentage) 
    {
        $template = isset($args['template']) ? $args['template'] : self::$TEMPLATE;

        return str_replace('%%PERCENTAGE%%', $percentage, $template);
    }

}