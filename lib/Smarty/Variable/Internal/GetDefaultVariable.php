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
class Smarty_Variable_Internal_GetDefaultVariable
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
     * @param Smarty | Smarty_Data $object
     */
    public function __construct($object)
    {
        $this->smarty = isset($object->smarty) ? $object->smarty : $object;
    }


    /**
     * try to get variable from registered default handler, create error or return null value
     *
     * @internal
     * @param  string $varname        the name of the Smarty variable
     * @param bool $error_enable
     * @throws Smarty_Exception_Runtime
     * @return mixed                  null|Smarty_variable object|property of variable
     */

    public function _getDefaultVariable($varname, $error_enable = true)
    {
        // template or config variable
        if (strpos($varname, '___config_var_') !== 0) {
            $is_config = false;
            $default_handler = $this->smarty->default_variable_handler_func;
        } else {
            $is_config = true;
            $default_handler = $this->smarty->default_config_variable_handler_func;
            $varname = substr($varname, 14);
        }
        // default handler registered?
        if (isset($default_handler)) {
            $value = null;
            if (call_user_func_array($default_handler, array($varname, &$value, $this->smarty))) {
                if ($is_config) {
                    return $value;
                } else {
                    if ($value instanceof Smarty_Variable) {
                        return $value;
                    } else {
                        return new Smarty_Variable($value);
                    }
                }
            }
        }
        // error message?
        if ($this->smarty->error_unassigned != Smarty::UNASSIGNED_IGNORE && $error_enable) {
            if ($is_config) {
                $err_msg = "Unassigned config variable '{$varname}'";
            } else {
                $err_msg = "Unassigned template variable '{$varname}'";
            }
            if ($this->smarty->error_unassigned == Smarty::UNASSIGNED_NOTICE) {
                // force a notice
                trigger_error($err_msg);
            } elseif ($this->smarty->error_unassigned == Smarty::UNASSIGNED_EXCEPTION) {
                throw new Smarty_Exception_Runtime($err_msg);
            }
        }
        // empty variable
        if ($is_config) {
            return null;
        } else {
            return new Smarty_Variable();
        }
    }
}