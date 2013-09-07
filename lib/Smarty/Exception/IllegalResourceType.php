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
class Smarty_Exception_IllegalInheritanceResourceType extends Smarty_Exception_Runtime
{
    public function __construct($type)
    {
        $message = sprintf("Illegal use of source resource type '%s' for template inheritance", $type);
        parent::__construct($message, 0);
    }
}
