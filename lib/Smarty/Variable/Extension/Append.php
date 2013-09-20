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
 * Class for static append method
 *
 * @package Smarty\Extension
 */
class Smarty_Variable_Extension_Append
{
    /**
     * appends values to template variables
     *
     * @internal
     * @param  Smarty | Smarty_Data   $holder  object with contains variable scope
     * @param  array|string $tpl_var the template variable name(s)
     * @param  mixed $value   the value to append
     * @param  boolean $merge   flag if array elements shall be merged
     * @param  boolean $nocache if true any output of this variable will be not cached
     * @return Smarty_Variable_Methods current Smarty_Variable_Methods (or Smarty) instance for chaining
     */
    public static function append($holder, $tpl_var, $value = null, $merge = false, $nocache = false)
    {
        if (is_array($tpl_var)) {
            // $tpl_var is an array, ignore $value
            foreach ($tpl_var as $varname => $_val) {
                if ($varname != '') {
                    if (!isset($holder->_tpl_vars->$varname)) {
                        $tpl_var_inst = Smarty_Variable_Extension_GetVariable::getVariable($holder, $varname, null, true, false);
                        if ($tpl_var_inst === null) {
                            $holder->_tpl_vars->$varname = new Smarty_Variable(null, $nocache);
                        } else {
                            $holder->_tpl_vars->$varname = clone $tpl_var_inst;
                        }
                    }
                    if (!(is_array($holder->_tpl_vars->$varname->value) || $holder->_tpl_vars->$varname->value instanceof ArrayAccess)) {
                        settype($holder->_tpl_vars->$varname->value, 'array');
                    }
                    if ($merge && is_array($_val)) {
                        foreach ($_val as $_mkey => $_mval) {
                            $holder->_tpl_vars->$varname->value[$_mkey] = $_mval;
                        }
                    } else {
                        $holder->_tpl_vars->$varname->value[] = $_val;
                    }
                }
            }
        } else {
            if ($tpl_var != '' && isset($value)) {
                if (!isset($holder->_tpl_vars->$tpl_var)) {
                    $tpl_var_inst = Smarty_Variable_Extension_GetVariable::getVariable($holder, $tpl_var, null, true, false, null, true);
                    if ($tpl_var_inst === null) {
                        $holder->_tpl_vars->$tpl_var = new Smarty_Variable(null, $nocache);
                    } else {
                        $holder->_tpl_vars->$tpl_var = clone $tpl_var_inst;
                    }
                }
                if (!(is_array($holder->_tpl_vars->$tpl_var->value) || $holder->_tpl_vars->$tpl_var->value instanceof ArrayAccess)) {
                    settype($holder->_tpl_vars->$tpl_var->value, 'array');
                }
                if ($merge && is_array($value)) {
                    foreach ($value as $_mkey => $_mval) {
                        $holder->_tpl_vars->$tpl_var->value[$_mkey] = $_mval;
                    }
                } else {
                    $holder->_tpl_vars->$tpl_var->value[] = $value;
                }
            }
        }
    }
}