<?php

/**
 * Smarty Extension Config Plugin
 *
 * Smarty filter methods
 *
 *
 * @package CoreExtensions
 * @author Uwe Tews
 */

/**
 * Class for modifier methods
 *
 *
 * @package CoreExtensions
 */
class Smarty_Extension_Config
{

    /**
     * Registers a default config variable handler
     *
     * @api
     * @param  Smarty $smarty   Smarty object
     * @param  callable $callback class/method name
     * @return Smarty
     * @throws Smarty_Exception if $callback is not callable
     */
    public function registerDefaultConfigVariableHandler(Smarty $smarty, $callback)
    {
        if (is_callable($callback)) {
            $smarty->default_config_variable_handler_func = $callback;
        } else {
            throw new Smarty_Exception("registerDefaultConfigVariableHandler(): Invalid callback");
        }

        return $smarty;
    }

    /**
     * Registers a default config variable handler
     *
     * @api
     * @param  Smarty $smarty   Smarty object
     * @return Smarty
     */
    public function unregisterDefaultConfigVariableHandler(Smarty $smarty)
    {
        $smarty->default_config_variable_handler_func = null;

        return $smarty;
    }


    /**
     * Registers a default config handler
     *
     * @api
     * @param  Smarty $smarty   Smarty object
     * @param  callable $callback class/method name
     * @return Smarty
     * @throws Smarty_Exception if $callback is not callable
     */
    public function registerDefaultConfigHandler(Smarty $smarty, $callback)
    {
        if (is_callable($callback)) {
            $smarty->default_config_handler_func = $callback;
        } else {
            throw new Smarty_Exception("registerDefaultConfigHandler(): Invalid callback");
        }

        return $smarty;
    }

    /**
     * Unregisters a default config handler
     *
     * @api
     * @param  Smarty $smarty   Smarty object
     * @return Smarty
     */
    public function unregisterDefaultConfigHandler(Smarty $smarty)
    {
        $smarty->default_config_handler_func = null;

        return $smarty;
    }
}
