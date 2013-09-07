<?php

/**
 * Smarty Exception Plugin
 *
 * @package Smarty\Exception
 */

/**
 * Smarty illegal resource exception
 *
 * @package Smarty\Exception
 */
class Smarty_Exception_UnknownResourceType extends Smarty_Exception_Runtime
{
    public function __construct($group, $type)
    {
        $foo = explode('_', $group);
        $message = sprintf("Unknown '%s' resource type '%s'", end($foo), $type);
        parent::__construct($message, 0);
    }
}
