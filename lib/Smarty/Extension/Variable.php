<?php

/**
 * Smarty Extension DefaultVariable Plugin
 *
 * Smarty filter methods
 *
 *
 * @package Smarty
 * @author Uwe Tews
 */

/**
 * Class for modifier methods
 *
 *
 * @package Smarty
 */
class Smarty_Extension_Variable
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

    /**
     * Unregisters a default variable handler
     *
     * @api
     * @return Smarty
     */
    public function unregisterDefaultVariableHandler()
    {
        $this->smarty->default_variable_handler_func = null;

        return $this->smarty;
    }

}
