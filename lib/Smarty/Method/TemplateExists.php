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
 * Class for templateExists method
 *
 * @package Smarty\Extension
 */
class Smarty_Method_TemplateExists
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
     * Check if a template resource exists
     *
     * @api
     * @param  string $template_resource template name
     * @return boolean status
     */
    public function templateExists($template_resource)
    {
        $context = Smarty_Context::getContext($this->smarty, $template_resource);
        return $context->exists;
    }
}
