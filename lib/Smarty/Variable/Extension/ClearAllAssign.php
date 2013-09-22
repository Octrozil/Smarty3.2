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
 * Class for static clearAssign method
 *
 * @package Smarty\Extension
 */
class Smarty_Variable_Extension_ClearAllAssign
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
    public function __construct($smarty)
    {
        $this->smarty = $smarty;
    }

    /**
     * clear all the assigned template variables.
     *
     * @api
     * @return Smarty_Variable_Methods current Smarty_Variable_Methods (or Smarty) instance for chaining
     */
    public function clearAllAssign()
    {
        $this->smarty->_tpl_vars = new Smarty_Variable_Scope();
        $this->smarty;
    }
}