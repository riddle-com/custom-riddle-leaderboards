<?php

namespace Riddle\Landingpage\Module\Shortcode;

class LeaderboardMissedPlaceShortcode extends ModuleShortcode
{

    private static $NAME = 'missed-place';
    private static $TEMPLATE = "You've missed out on the %%PLACE%% by %%PERCENTAGE%%% - try again to be one of the best.";
    private static $PLACE = 10; // default: top 10
    private static $PLACE_NAME = 'top 10';

    public function __construct($module) 
    {
        parent::__construct(self::$NAME, $module);
    }

    public function render(array $args)
    {
        if (!$this->module->getApp()->hasData()) {
            return '';
        }

        $placement = $this->module->getHelperService()->getPlacementByData($this->module->getApp()->getData());
        $missedPlace = $this->_getPlace($args);

        /**
         * Return an empty string 
         * if the placement is better than the missed place 
         * OR
         * if the total entries is not as big as the missed place
         */
        if ($placement <= $missedPlace || $missedPlace > $this->module->getStoreService()->getTotalEntries()) {
            return '';
        }

        $percentageBorder = $this->module->getStoreService()->getEntry($missedPlace - 1)['percentage'];
        $percentageAchieved = $this->module->getStoreService()->getEntry($placement - 1)['percentage'];

        $missedBy = 100 - floor($percentageAchieved / ($percentageBorder / 100));
        $replacements = [
            'percentage' => $missedBy,
            'place' => $this->_getPlaceName($args)
        ];

        return $this->_getTemplate($args, $replacements);
    }

    private function _getPlace(array $args)
    {
        return isset($args['place']) && is_numeric($args['place']) 
            ? $args['place'] 
            : self::$PLACE;
    }

    private function _getPlaceName(array $args) 
    {
        return isset($args['placeName']) && is_string($args['placeName']) 
            ? $args['placeName'] 
            : self::$PLACE_NAME;
    }

    protected function _getTemplate(array $args, $replacements) 
    {
        $template = isset($args['template']) ? $args['template'] : self::$TEMPLATE;

        foreach ($replacements as $search => $replace) {
            $template = str_ireplace('%%' . $search . '%%', $replace, $template);
        }

        return $template;
    }

}