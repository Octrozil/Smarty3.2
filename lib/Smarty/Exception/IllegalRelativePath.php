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
class Smarty_Exception_IllegalRelativePath extends Smarty_Exception_Runtime
{
    public function __construct($file, $type)
    {
        $message = sprintf("Template '%s' cannot be relative to template of resource type '%s'", $file, $type);
        parent::__construct($message, 0);
    }
}
