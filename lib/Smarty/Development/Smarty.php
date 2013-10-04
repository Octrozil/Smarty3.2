<?php

/**
 * Smarty Logger
 *
 * This file contains the Smarty Logger
 *
 *
 * @package Smarty
 * @author Uwe Tews
 */

/**
 * class for the Smarty Logger object
 *
 * This Class handles logging of events
 *
 *
 * @package Smarty
 */


class Smarty_Development_Smarty extends Smarty_Development_Logger
{
    public $smarty = null;

    public $template = array();

    /**
     * create Smarty Logger object
     *
     * @param  Smarty $smarty     object of Smarty instance
     */
    public function __construct()
    {
    }

}
