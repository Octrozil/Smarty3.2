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
 * Class for static clearConfig method
 *
 * @package Smarty\Extension
 */
class Smarty_Variable_Method_ClearConfig
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
     * Deassigns a single or all config variables
     *
     * @api
     * @param  string $varname variable name or null
     * @return Smarty_Variable_Methods current Smarty_Variable_Methods (or Smarty) instance for chaining
     */
    public function clearConfig($varname = null)
    {
        if (isset($varname)) {
            unset($this->smarty->_tpl_vars->{'___config_var_' . $varname});
        } else {
            foreach ($this->smarty->_tpl_vars as $key => $var) {
                if (strpos($key, '___config_var_') === 0) {
                    unset($this->smarty->_tpl_vars->$key);
                }
            }
        }

        return $this->smarty;
    }
}