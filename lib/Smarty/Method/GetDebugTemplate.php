<?php

/**
 * Smarty Extension
 *
 * Smarty class methods
 *
 * @package Smarty\Extension
 * @author Uwe Tews
 */

/**
 * Class for getDebugTemplate method
 *
 * @package Smarty\Extension
 */
class Smarty_Method_GetDebugTemplate
{
    /**
     *  Smarty object
     *
     * @var Smarty
     */
    public $smarty;

    /**
     *  Constructor
     *
     * @param Smarty $smarty Smarty object
     */
    public function __construct(Smarty $smarty)
    {
        $this->smarty = $smarty;
    }
    /**
     * return name of debugging template
     *
     * @api
     * @return string
     */
    public function getDebugTemplate()
    {
        return $this->debug_tpl;
    }
}
