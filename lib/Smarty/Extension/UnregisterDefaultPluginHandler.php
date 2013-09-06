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
 * Class for unregisterDefaultPluginHandler method
 *
 * @package Smarty\Extension
 */
class Smarty_Extension_UnregisterDefaultPluginHandler
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
     * Unregisters a default plugin handler
     *
     * @api
     * @return Smarty
     */
    public function unregisterDefaultPluginHandler()
    {
        $this->smarty->default_plugin_handler_func = null;

        return $this->smarty;
    }
}
