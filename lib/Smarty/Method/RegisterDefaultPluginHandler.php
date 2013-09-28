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
 * Class for registerDefaultPluginHandler method
 *
 * @package Smarty\Extension
 */
class Smarty_Method_RegisterDefaultPluginHandler
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
     * Registers a default plugin handler
     *
     * @api
     * @param  callable $callback class/method name
     * @return Smarty
     * @throws Smarty_Exception if $callback is not callable
     */
    public function registerDefaultPluginHandler($callback)
    {
        if (is_callable($callback)) {
            $this->smarty->default_plugin_handler_func = $callback;
        } else {
            throw new Smarty_Exception("registerDefaultPluginHandler(): Invalid callback");
        }

        return $this->smarty;
    }
}
