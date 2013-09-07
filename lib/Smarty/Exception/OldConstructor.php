<?php

/**
 * Smarty Exception Plugin
 *
 * @package Smarty\Exception
 */

/**
 * Smarty old constructor exception
 *
 * @package Smarty\Exception
 */
class Smarty_Exception_OldConstructor extends Smarty_Exception_Runtime
{
    public function __construct()
    {
        $message = "PHP5 requires you to call __construct() instead of Smarty()";
        parent::__construct($message, 0);
    }
}
