<?php

/**
 * Smarty Exception Plugin
 *
 * @package Smarty\Exception
 */

/**
 * Smarty runtime capture exception
 *
 * @package Smarty\Exception
 */
class Smarty_Exception_CaptureError extends Smarty_Exception_Runtime
{
    public function __construct($message = null, $code = 0)
    {
        if (!isset($message)) {
            $message = "{capture}: Not matching open/close tags";
        }
        parent::__construct($message, $code);
    }
}
