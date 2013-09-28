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
 * Class for registerDefaultConfigHandler method
 *
 * @package Smarty\Extension
 */
class Smarty_Method_RegisterDefaultConfigHandler
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
}
