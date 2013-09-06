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
 * Class for unegisterDefaultConfigVariableHandler method
 *
 * @package Smarty\Extension
 */
class Smarty_Extension_UnregisterDefaultConfigVariableHandler
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
     * @return Smarty
     */
    public function unregisterDefaultConfigVariableHandler()
    {
        $this->smarty->default_config_variable_handler_func = null;

        return $this->smarty;
    }
}
