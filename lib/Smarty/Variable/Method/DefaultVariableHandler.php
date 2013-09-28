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
 * Class for static getDefaultVariable method
 *
 * @package Smarty\Extension
 */
class Smarty_Variable_Method_DefaultVariableHandler
{
    /**
     * get the object or property of default template variable
     *
     * @internal
     * @param  Smarty $smarty         Smarty object
     * @param  string $varname        the name of the Smarty variable
     * @param  null $property         optional requested variable property
     * @param bool $error_enable
     * @throws Smarty_Exception_Runtime
     * @return mixed                  null|Smarty_variable object|property of variable
     */

    public static function getDefaultVariable($smarty, $varname, $property = null, $error_enable = true) {
        if (isset($smarty->smarty)) {
            $smarty = $smarty->smarty;
        }
        $error_unassigned = $smarty->error_unassigned;
        if (strpos($varname, '___config_var_') !== 0) {
            if (isset($smarty->default_variable_handler_func)) {
                $value = null;
                if (call_user_func_array($smarty->default_variable_handler_func, array($varname, &$value, $smarty))) {
                    if ($value instanceof Smarty_Variable) {
                        $var = $value;
                    } else {
                        $var = new Smarty_Variable($value);
                    }
                    if ($property === null) {
                        return $var;
                    } else {
                        return isset($var->$property) ? $var->$property : null;
                    }
                }
            }
            if ($error_unassigned != Smarty::UNASSIGNED_IGNORE && $error_enable) {
                $err_msg = "Unassigned template variable '{$varname}'";
                if ($error_unassigned == Smarty::UNASSIGNED_NOTICE) {
                    // force a notice
                    trigger_error($err_msg);
                } elseif ($error_unassigned == Smarty::UNASSIGNED_EXCEPTION) {
                    throw new Smarty_Exception_Runtime($err_msg);
                }
            }
            $var = new Smarty_Variable();
            if ($property === null) {
                return $var;
            } else {
                return isset($var->$property) ? $var->$property : null;
            }

        } else {
            $real_varname = substr($varname, 14);
            if (isset($smarty->default_config_variable_handler_func)) {
                $value = null;
                if (call_user_func_array($smarty->default_config_variable_handler_func, array($real_varname, &$value, $smarty))) {
                    return $value;
                }
            }
            if ($error_unassigned != Smarty::UNASSIGNED_IGNORE && $error_enable) {
                $err_msg = "Unassigned config variable '{$real_varname}'";
                if ($error_unassigned == Smarty::UNASSIGNED_NOTICE) {
                    // force a notice
                    trigger_error($err_msg);
                } elseif ($error_unassigned == Smarty::UNASSIGNED_EXCEPTION) {
                    throw new Smarty_Exception_Runtime($err_msg);
                }
            }
        }
        // unassigned variable which shall be ignored
        return null;
    }
}