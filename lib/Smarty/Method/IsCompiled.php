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
 * Class for isCompiled method
 *
 * @package Smarty\Extension
 */
class Smarty_Method_IsCompiled
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
     * @param Smarty $smarty
     */
    public function __construct(Smarty $smarty)
    {
        $this->smarty = $smarty;
    }

    /**
     * test if compiled template is valid
     *
     * @api
     * @param  string|object $template   the resource handle of the template file or template object
     * @param  mixed $compile_id compile id to be used with this template
     * @param  null $caching
     * @throws Smarty_Exception_SourceNotFound
     * @throws Exception
     * @return boolean       compilation status
     */
    public function isCompiled($template = null, $compile_id = null, $caching = null)
    {
        if ($this->smarty->force_compile) {
            return false;
        }
        if ($template === null && ($this->smarty->_usage == Smarty::IS_SMARTY_TPL_CLONE || $this->smarty->_usage == Smarty::IS_CONFIG)) {
            $template = $this->smarty;
        }
        //get source object from cache  or create new one
        $context = Smarty_Context::getContext($this->smarty, $template, null, $compile_id, null, false, null, null, null, $caching);
        // checks if source exists
        if (!$context->exists) {
            throw new Smarty_Exception_SourceNotFound($context->type, $context->name);
        }
        if ($context->handler->recompiled) {
            // recompiled return always false
            return false;
        }
        if ($context->handler->uncompiled) {
            // uncompiled source returns always true
            return true;
        }
        $res_obj = $this->smarty->_loadResource(Smarty::COMPILED, $this->smarty->compiled_type);
        $timestamp = $exists = false;
        $filepath = $res_obj->buildFilepath($context);
        $res_obj->populateTimestamp($this->smarty, $filepath, $timestamp, $exists);
        if (!$exists || $timestamp < $context->timestamp) {
            return false;
        }
        try {
            $template_obj = $context->_getTemplateObject(Smarty::COMPILED, false);
            if ($template_obj === false) {
                return false;
            }
            return $template_obj->isValid;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
