<?php

namespace Riddle\Landingpage\Module\Shortcode;

abstract class ModuleShortcode
{

    private $name;
    protected $module;

    public function __construct($name, $module) 
    {
        $this->name = $name;
        $this->module = $module;
    }

    abstract function render(array $args);

    public function getName()
    {
        return $this->name;
    }

}