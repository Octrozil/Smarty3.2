<?php

/**
 * Smarty Extension Config Plugin
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
class Smarty_Extension_Config
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
     * Registers a default config variable handler
     *
     * @api
     * @param  callable $callback class/method name
     * @return Smarty
     * @throws Smarty_Exception if $callback is not callable
     */
    public function registerDefaultConfigVariableHandler($callback)
    {
        if (is_callable($callback)) {
            $this->smarty->default_config_variable_handler_func = $callback;
        } else {
            throw new Smarty_Exception("registerDefaultConfigVariableHandler(): Invalid callback");
        }

        return $this->smarty;
    }

    /**
     * Registers a default config variable handler
     *
     * @api
     * @return Smarty
     */
    public function unregisterDefaultConfigVariableHandler()
    {
        $this->smarty->default_config_variable_handler_func = null;

        return $this->smarty;
    }


    /**
     * Registers a default config handler
     *
     * @api
     * @param  callable $callback class/method name
     * @return Smarty
     * @throws Smarty_Exception if $callback is not callable
     */
    public function registerDefaultConfigHandler($callback)
    {
        if (is_callable($callback)) {
            $this->smarty->default_config_handler_func = $callback;
        } else {
            throw new Smarty_Exception("registerDefaultConfigHandler(): Invalid callback");
        }

        return $this->smarty;
    }

    /**
     * Unregisters a default config handler
     *
     * @api
     * @return Smarty
     */
    public function unregisterDefaultConfigHandler()
    {
        $this->smarty->default_config_handler_func = null;

        return $this->smarty;
    }
}
