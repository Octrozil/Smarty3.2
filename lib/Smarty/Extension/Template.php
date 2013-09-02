<?php

/**
 * Smarty Extension Template Plugin
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
class Smarty_Extension_Template
{
    /**
     * Registers a default template handler
     *
     * @api
     * @param  Smarty $smarty   Smarty object
     * @param  callable $callback class/method name
     * @return Smarty
     * @throws Smarty_Exception if $callback is not callable
     */
    public function registerDefaultTemplateHandler(Smarty $smarty, $callback)
    {
        if (is_callable($callback)) {
            $smarty->default_template_handler_func = $callback;
        } else {
            throw new Smarty_Exception("registerDefaultTemplateHandler(): Invalid callback");
        }
        return $smarty;
    }

    /**
     * Registers a default template handler
     *
     * @api
     * @param  Smarty $smarty   Smarty object
     * @return Smarty
     * @throws Smarty_Exception if $callback is not callable
     */
    public function unregisterDefaultTemplateHandler(Smarty $smarty)
    {
        $smarty->default_template_handler_func = null;

        return $smarty;
    }

    /**
     * Check if a template resource exists
     *
     * @api
     * @param  Smarty $smarty   Smarty object
     * @param  string $template_resource template name
     * @param  bool  $is_config set true if looking for a config file
     * @return boolean status
     */
    public function templateExists(Smarty $smarty, $template_resource, $is_config = false)
    {
        $source = Smarty_Resource_Loader::load($smarty, Smarty_Resource_Loader::SOURCE, $template_resource);
        $source->usage = $is_config ? Smarty::IS_CONFIG : Smarty::IS_TEMPLATE;
        $source->populate($smarty);
        return $source->exists;
    }

}
