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
 * Class for unregisterDefaultVariableHandler method
 *
 * @package Smarty\Extension
 */
class Smarty_Method_UnregisterDefaultVariableHandler
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
