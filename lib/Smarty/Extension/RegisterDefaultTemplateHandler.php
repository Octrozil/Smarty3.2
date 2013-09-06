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
 * Class for registerDefaultTemplateHandler method
 *
 * @package Smarty\Extension
 */
class Smarty_Extension_RegisterDefaultTemplateHandler
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
     * @param  callable $callback class/method name
     * @return Smarty
     * @throws Smarty_Exception if $callback is not callable
     */
    public function registerDefaultTemplateHandler($callback)
    {
        if (is_callable($callback)) {
            $this->smarty->default_template_handler_func = $callback;
        } else {
            throw new Smarty_Exception("registerDefaultTemplateHandler(): Invalid callback");
        }
        return $this->smarty;
    }
}
