<?php

/**
 * Smarty Exception Plugin
 *
 * @package Smarty\Exception
 */

/**
 * Smarty relative source not found exception
 *
 * @package Smarty\Exception
 */
class Smarty_Exception_RelativeSourceNotFound extends Smarty_Exception_Runtime
{
    public function __construct($type, $name)
    {
        $message = sprintf("Can not find relative source '%s:%s'", $type, $name);
        parent::__construct($message, 0);
    }
}
