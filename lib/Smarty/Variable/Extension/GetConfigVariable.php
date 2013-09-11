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
 * Class for static getConfigVars method
 *
 * @package Smarty\Extension
 */
class Smarty_Variable_Extension_GetConfigVariable
{
    /**
     * Returns a single or all config variables
     *
     * @internal
     * @param  Smarty | Smarty_Data   $holder  object with contains variable scope
     * @param  string $varname        variable name or null
     * @param  boolean $search_parents include parent templates?
     * @return string  variable value or or array of variables
     */
     public static function getConfigVars($holder, $varname = null, $search_parents = true)
    {
        $_ptr = $holder;
        if (isset($varname)) {
            $result = Smarty_Variable_Extension_GetVariable::getVariable($holder, '___config_var_' . $varname, $_ptr, $search_parents, false);

            return $result;
        } else {
            $_result = array();
            while ($_ptr !== null) {
                foreach ($_ptr->_tpl_vars AS $varname => $data) {
                    $real_varname = substr($varname, 14);
                    if (strpos($varname, '___config_var_') === 0 && !isset($_result[$real_varname])) {
                        $_result[$real_varname] = $data;
                    }
                }
                // not found, try at parent
                if ($search_parents) {
                    $_ptr = $_ptr->parent;
                } else {
                    $_ptr = null;
                }
            }

            return $_result;
        }
    }

}