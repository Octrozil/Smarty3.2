<?php

/**
 * Smarty Extension Template Plugin
 *
 * Smarty filter methods
 *
 *
 * @package Smarty
 * @author Uwe Tews
 */

/**
 * Class for modifier methods
 *
 *
 * @package Smarty
 */
class Smarty_Extension_Template
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

    /**
     * Check if a template resource exists
     *
     * @api
     * @param  string $template_resource template name
     * @param  bool $is_config set true if looking for a config file
     * @return boolean status
     */
    public function templateExists($template_resource, $is_config = false)
    {
        $source = Smarty_Resource_Loader::load($this->smarty, Smarty_Resource_Loader::SOURCE, $template_resource);
        $source->usage = $is_config ? Smarty::IS_CONFIG : Smarty::IS_TEMPLATE;
        $source->populate($this->smarty);
        return $source->exists;
    }

}
