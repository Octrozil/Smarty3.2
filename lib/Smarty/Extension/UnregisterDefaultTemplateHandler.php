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
 * Class for unregisterDefaultTemplateHandler method
 *
 * @package Smarty\Extension
 */
class Smarty_Extension_UnregisterDefaultTemplateHandler
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
     * Registers a default template handler
     *
     * @api
     * @return Smarty
     * @throws Smarty_Exception if $callback is not callable
     */
    public function unregisterDefaultTemplateHandler()
    {
        $this->smarty->default_template_handler_func = null;

        return $this->smarty;
    }
}
