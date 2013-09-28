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
 * Class for getGlobal method
 *
 * @package Smarty\Extension
 */
class Smarty_Method_GetGlobal
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
     * Returns a single or all global  variables
     *
     * @param  string $varname variable name or null
     * @return string variable value or or array of variables
     */
    public function getGlobal($varname = null)
    {
        if (isset($varname)) {
            if (isset(Smarty::$_global_tpl_vars->{$varname}->value)) {
                return Smarty::$_global_tpl_vars->{$varname}->value;
            } else {
                return '';
            }
        } else {
            $_result = array();
            foreach (Smarty::$_global_tpl_vars AS $key => $var) {
                if (strpos($key, '___') !== 0) {
                    $_result[$key] = $var->value;
                }
            }

            return $_result;
        }
    }
}
