<?php

/**
 * Smarty Exception Plugin
 *
 * @package Smarty\Exception
 */

/**
 * Smarty default handler not callable  exception
 *
 * @package Smarty\Exception
 */
class Smarty_Exception_DefaultHandlerNotCallable extends Smarty_Exception_Runtime
{
    public function __construct($type)
    {
        $message = sprintf("Default %s handler not callable", $type);
        parent::__construct($message, 0);
    }
}
