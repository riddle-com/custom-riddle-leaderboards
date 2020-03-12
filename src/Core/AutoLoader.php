<?php

/**
 * @since 1.0
 * 
 * You can ignore this class since it's only a normal autoloader which simplifies implementing classes into other classes.
 * The following namespace naming convention has to be followed:
 * 
 * Example: given class in src/Example/Helloworld.php
 * Namespace => Riddle\Example\HelloWorld
 * The prefix Riddle is only there to make sure that we haven't got any namespace duplicates.
 */

namespace Riddle\Core;

class Autoloader 
{

    public static function loadClass($className) 
    {
        if (class_exists($className)) {
            return false;
        }

        $className = str_replace ('\\', '/', $className);
        $className = str_replace('Riddle/', '', $className);
        $classPath = SRC_DIR . '/' . $className . '.php';


        if (file_exists($classPath)) {
            require_once($classPath);

            return true;
        }

        return false;
    }

}