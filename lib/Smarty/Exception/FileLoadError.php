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
class Smarty_Exception_FileLoadError extends Smarty_Exception_Runtime
{
    /**
     * @param string $type
     * @param int|null $file
     */
    public function __construct($type, $file)
    {
        $message = sprintf("Unable to load %s file '%s'", $type, $file);
        parent::__construct($message, 0);
    }
}
