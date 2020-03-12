<?php

namespace Riddle\Landingpage\Module\Block;

abstract class ModuleBlock
{
    
    protected $module;

    public function __construct($module)
    {
        $this->module = $module;
    }

    abstract function render(array $args);

    private function _extendsWebhookModule($module)
    {
        return is_subclass_of($module, 'Riddle\Landingpage\Module\WebhookModule');
    }

    /**
     * @return (RiddleData|boolean) returns null if no riddle data exists
     */
    protected function _getData()
    {
        return $this->module->getApp()->getData();
    }

}