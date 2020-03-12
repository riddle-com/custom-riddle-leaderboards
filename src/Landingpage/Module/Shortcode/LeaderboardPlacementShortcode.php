<?php

/**
 * @since 1.0
 */

namespace Riddle\Landingpage\Module\Shortcode;

class LeaderboardPlacementShortcode extends ModuleShortcode
{

    private static $NAME = 'placement';
    private static $TEMPLATE = "Your placement: %%PLACEMENT%% out of %%TOTAL%%";
    private static $DEFAULT_FIRST_PLACE_NAMES = ['first', 'second', 'third'];
    private static $DEFAULT_FIRST_PLACE_SHORT_NAMES = ['st', 'nd', 'rd'];

    public function __construct($module) 
    {
        parent::__construct(self::$NAME, $module);
    }

    public function render(array $args)
    {
        if (!$this->module->getApp()->hasData()) {
            return '';
        }

        $totalEntries = $this->module->getStoreService()->getTotalEntries();
        $replacements = [
            'total' => $totalEntries,
            'placement' => $this->_getPlacementString($args),
        ];

        return $this->_getTemplate($args, $replacements);
    }

    protected function _getTemplate(array $args, $replacements) 
    {
        $template = isset($args['template']) ? $args['template'] : self::$TEMPLATE;

        foreach ($replacements as $search => $replace) {
            $template = str_ireplace('%%' . $search . '%%', $replace, $template);
        }

        return $template;
    }

    private function _getPlacementString(array $args) 
    {
        $placement = $this->module->getHelperService()->getPlacementByData($this->module->getApp()->getData());

        return $placement < 3
            ? $this->_getFirstPlaceNames($args)[$placement]
            : $this->_getFirstPlaceShortName($placement + 1, $args); // to prevent e.g. 101th and write "101st"
    }

    private function _getFirstPlaceShortName($placement, array $args) 
    {
        $mod = $placement % 10;

        return $mod <= 3 && $mod > 0
            ? $placement . $this->_getFirstPlaceShortNames($args)[$mod-1]
            : $placement . 'th';
    }

    private function _getFirstPlaceShortNames(array $args)
    {
        if (!isset($args['firstPlaceShortNames'])) {
            return self::$DEFAULT_FIRST_PLACE_SHORT_NAMES;
        }

        if(!is_array($args['firstPlaceShortNames']) || count($args['firstPlaceShortNames']) !== 3) {
            throw new \InvalidArgumentException('The argument \'firstPlaceShortNames\' has to be an array and has to be three items long.');
        }

        return $args['firstPlaceNames'];
    }

    private function _getFirstPlaceNames(array $args)
    {
        if (!isset($args['firstPlaceNames'])) {
            return self::$DEFAULT_FIRST_PLACE_NAMES;
        }

        if(!is_array($args['firstPlaceNames']) || count($args['firstPlaceNames']) !== 3) {
            throw new \InvalidArgumentException('The argument \'firstPlaceNames\' has to be an array and has to have the length of 3.');
        }

        return $args['firstPlaceNames'];
    }

}