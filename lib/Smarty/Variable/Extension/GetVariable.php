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
 * Class for static getVariable and getTemplateVars method
 *
 * @package Smarty\Extension
 */
class Smarty_Variable_Extension_GetVariable
{
    /**
     * Returns a single or all template variables
     *
     * @internal
     * @param  Smarty | Smarty_Data   $holder  object with contains variable scope
     * @param  string $varname        variable name or null
     * @param  string $_ptr           optional pointer to data object
     * @param  boolean $search_parents include parent templates?
     * @return string  variable value or or array of variables
     */
    public static function getTemplateVars($holder, $varname = null, $_ptr = null, $search_parents = true)
    {
        if (isset($varname)) {
            $result = self::getVariable($holder, $varname, $_ptr, $search_parents, false);
            if ($result === null) {
                return false;
            } else {
                return $result->value;
            }
        } else {
            $_result = array();
            if ($_ptr === null) {
                $_ptr = $holder;
            }
            while ($_ptr !== null) {
                foreach ($_ptr->_tpl_vars AS $varname => $data) {
                    if (strpos($varname, '___') !== 0 && !isset($_result[$varname])) {
                        $_result[$varname] = $data->value;
                    }
                }
                // not found, try at parent
                if ($search_parents) {
                    $_ptr = $_ptr->parent;
                } else {
                    $_ptr = null;
                }
            }
            if ($search_parents && isset(Smarty::$_global_tpl_vars)) {
                foreach (Smarty::$_global_tpl_vars AS $varname => $data) {
                    if (strpos($varname, '___') !== 0 && !isset($_result[$varname])) {
                        $_result[$varname] = $data->value;
                    }
                }
            }

            return $_result;
        }
    }

    /**
     * gets the object of a template variable
     *
     * @internal
     * @param  Smarty | Smarty_Data   $holder  object with contains variable scope
     * @param  string $varname        the name of the Smarty variable
     * @param  object $_ptr           optional pointer to data object
     * @param  boolean $search_parents search also in parent data
     * @param  boolean $error_enable   enable error handling
     * @param  null $property       optional requested variable property
     * @return mixed                    Smarty_variable object|property of variable
     */
    public static function getVariable($holder, $varname, $_ptr = null, $search_parents = true, $error_enable = true, $property = null)
    {
        if ($_ptr === null) {
            $_ptr = $holder;
        }
        while ($_ptr !== null) {
            if (isset($_ptr->_tpl_vars->$varname)) {
                // found it, return it
                if ($property === null) {
                    return $_ptr->_tpl_vars->$varname;
                } else {
                    return isset($_ptr->_tpl_vars->$varname->$property) ? $_ptr->_tpl_vars->$varname->$property : null;
                }
            }
            // not found, try at parent
            if ($search_parents) {
                $_ptr = $_ptr->parent;
            } else {
                $_ptr = null;
            }
        }

        // try global variable
        if (null !== $var = Smarty_Variable_Methods::getGlobalVariable($varname, $property)) {
            return $var;
        }

        // try default variable
        return Smarty_Variable_Extension_DefaultVariableHandler::getDefaultVariable($holder, $varname, $property, $error_enable);
    }
}