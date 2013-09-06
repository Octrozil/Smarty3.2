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
 * Class for unregisterDefaultConfigHandler method
 *
 * @package Smarty\Extension
 */
class Smarty_Extension_UnregisterDefaultConfigHandler
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
