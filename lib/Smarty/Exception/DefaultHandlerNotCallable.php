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
    /**
     * @param string $type
     */
    public function __construct($type)
    {
        $message = sprintf("Default '%s' file handler not callable", $type);
        parent::__construct($message, 0);
    }
}
