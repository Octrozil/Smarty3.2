<?php

/**
 * Smarty Extension
 *
 * Smarty class methods
 *
 * @package Smarty\Variable
 * @author Uwe Tews
 */

/**
 * Class for static clearAssign method
 *
 * @package Smarty\Variable
 */
class Smarty_Variable_Method_ClearAllAssign
{
    /**
     *  Master object
     *
     * @var Smarty | Smarty_Data | Smarty_Template
     */
    public $object;

    /**
     *  Constructor
     *
     * @param Smarty | Smarty_Data | Smarty_Template $object master object
     */
    public function __construct($object)
    {
        $this->object = $object;
    }


    /**
     * clear all the assigned template variables.
     *
     * @api
     * @return Smarty_Variable_Methods current Smarty_Variable_Methods (or Smarty) instance for chaining
     */
    public function clearAllAssign()
    {
        $this->object->_tpl_vars = new Smarty_Variable_Scope();
        $this->object;
    }
}