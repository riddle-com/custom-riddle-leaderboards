<?php

namespace Riddle\Landingpage\Module\Shortcode;

class ModuleShortcodeManager
{

    private $shortcodes;

    public function __construct()
    {
        $this->shortcodes = [];
    }

    public function add(ModuleShortcode $code)
    {
        $this->shortcodes[$code->getName()] = $code;
    }

    public function render($shortcodeName, array $args)
    {
        if (!isset($this->shortcodes[$shortcodeName])) {
            return 'Unknown shortcode: ' . $shortcodeName;
        }

        return $this->shortcodes[$shortcodeName]->render($args);
    }

}