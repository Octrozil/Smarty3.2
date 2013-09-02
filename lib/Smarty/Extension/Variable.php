<?php

/**
 * Smarty Extension DefaultVariable Plugin
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
class Smarty_Extension_Variable
{


    /**
     * Registers a default variable handler
     *
     * @api
     * @param  Smarty $smarty   Smarty object
     * @param  callable $callback class/method name
     * @return Smarty
     * @throws Smarty_Exception if $callback is not callable
     */
    public function registerDefaultVariableHandler(Smarty $smarty, $callback)
    {
        if (is_callable($callback)) {
            $smarty->default_variable_handler_func = $callback;
        } else {
            throw new Smarty_Exception("registerDefaultVariableHandler(): Invalid callback");
        }

        return $smarty;
    }

    /**
     * Unregisters a default variable handler
     *
     * @api
     * @param  Smarty $smarty   Smarty object
     * @return Smarty
     */
    public function unregisterDefaultVariableHandler(Smarty $smarty)
    {
        $smarty->default_variable_handler_func = null;

        return $smarty;
    }

}
