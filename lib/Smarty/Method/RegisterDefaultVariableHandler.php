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
 * Class for registerDefaultVariableHandler method
 *
 * @package Smarty\Extension
 */
class Smarty_Method_registerDefaultVariableHandler
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
     * Registers a default variable handler
     *
     * @api
     * @param  callable $callback class/method name
     * @return Smarty
     * @throws Smarty_Exception if $callback is not callable
     */
    public function registerDefaultVariableHandler($callback)
    {
        if (is_callable($callback)) {
            $this->smarty->default_variable_handler_func = $callback;
        } else {
            throw new Smarty_Exception("registerDefaultVariableHandler(): Invalid callback");
        }

        return $this->smarty;
    }
}
