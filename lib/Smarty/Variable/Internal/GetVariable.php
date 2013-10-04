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
 * Class for static getVariable method
 *
 * @package Smarty\Extension
 */
class Smarty_Variable_Internal_GetVariable
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
     * gets the object of a template variable
     *
     * @internal
     * @param  string $varname        the name of the Smarty variable
     * @param  object $_ptr           optional pointer to data object
     * @param  boolean $search_parents search also in parent data
     * @param  boolean $error_enable   enable error handling
     * @param  boolean $disable_default       if true disable default handler
     * @return mixed                    Smarty_variable object|property of variable
     */
    public function _getVariable($varname, $_ptr = null, $search_parents = true, $error_enable = true, $disable_default = false)
    {
        if ($_ptr === null) {
            $_ptr = $this->smarty;
        }
        while ($_ptr !== null) {
            if (isset($_ptr->_tpl_vars->$varname)) {
                // found it, return it
                return $_ptr->_tpl_vars->$varname;
            }
            // not found, try at parent
            if ($search_parents) {
                $_ptr = $_ptr->parent;
            } else {
                $_ptr = null;
            }
        }

        // try global variable
        if (isset(Smarty::$_global_tpl_vars->$varname)) {
            // found it, return it
            return Smarty::$_global_tpl_vars->$varname;
        }

        if ($disable_default) {
            return null;
        } else {
            // try default variable
            return $this->smarty->_getDefaultVariable($varname, $error_enable);
        }
    }
}