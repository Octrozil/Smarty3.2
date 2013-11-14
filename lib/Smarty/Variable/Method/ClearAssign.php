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
class Smarty_Variable_Method_ClearAssign
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
     * clear the given assigned template variable.
     *
     * @api
     * @param  string|array $tpl_var the template variable(s) to clear
     * @return Smarty_Variable_Methods current Smarty_Variable_Methods (or Smarty) instance for chaining
     */
    public function clearAssign($tpl_var)
    {
        if (is_array($tpl_var)) {
            foreach ($tpl_var as $curr_var) {
                unset($this->smarty->_tpl_vars->$curr_var);
            }
        } else {
            unset($this->smarty->_tpl_vars->$tpl_var);
        }

        return $this->smarty;
    }

}