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
 * Class for assignCallback method
 *
 * @package Smarty\Extension
 */
class Smarty_Variable_Method_AssignCallback
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
     * assigns a variable hook
     *
     * @api
     * @param  string $varname the variable name
     * @param  callback $callback PHP callback to get variable value
     * @param  boolean $nocache if true any output of this variable will be not cached
     * @param int $scope_type
     * @throws Smarty_Exception
     * @return Smarty_Variable_Methods current Smarty_Variable_Methods (or Smarty) instance for chaining
     */
    public function assignCallback($varname, $callback, $nocache = false, $scope_type = Smarty::SCOPE_LOCAL)
    {
        if ($varname != '') {
            if (!is_callable($callback)) {
                throw new Smarty_Exception("assignHook(): Hook for variable \"{$varname}\" not callable");
            } else {
                if (is_object($callback)) {
                    $callback = array($callback, '__invoke');
                }
                $this->smarty->_assignInScope($varname, new Smarty_Variable_Callback($varname, $callback, $nocache), $scope_type);
            }
        }
        return $this->smarty;
    }
}
