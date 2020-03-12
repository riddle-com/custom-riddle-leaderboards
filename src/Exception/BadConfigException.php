<?php

/**
 * @since 1.0
 * 
 * This exception will be thrown if the users config is faulty: 
 *  - unavailable store method
 *  - used view doesn't exist
 *  - ...
 */

namespace Riddle\Exception;

class BadConfigException extends \Exception
{
}