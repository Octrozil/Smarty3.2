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
 * Class for isCached method
 *
 * @package Smarty\Extension
 */
class Smarty_Method_IsCached
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
     * test if cache is valid
     *
     * @api
     * @param  string|object $template   the resource handle of the template file or template object
     * @param  mixed $cache_id   cache id to be used with this template
     * @param  mixed $compile_id compile id to be used with this template
     * @param  object $parent     next higher level of Smarty variables
     * @throws Smarty_Exception
     * @return boolean       cache status
     */
    public function isCached($template = null, $cache_id = null, $compile_id = null, $parent = null)
    {
        {
            if ($this->smarty->force_cache || $this->smarty->force_compile || !($this->smarty->caching == Smarty::CACHING_LIFETIME_CURRENT || $this->smarty->caching == Smarty::CACHING_LIFETIME_SAVED)) {
                // caching is disabled
                return false;
            }
            if ($template === null && ($this->smarty->_usage == Smarty::IS_SMARTY_TPL_CLONE || $this->smarty->_usage == Smarty::IS_CONFIG)) {
                $template = $this->smarty;
            }
            //get source object from cache  or create new one
            $context = $this->smarty->_getContext($template, $cache_id, $compile_id, $parent, true);
            // checks if source exists
            if (!$context->exists) {
                throw new Smarty_Exception_SourceNotFound($context->type, $context->name);
            }
            if ($context->handler->recompiled) {
                // recompiled source can't be cached
                return false;
            }
            $tpl_obj = $context->smarty;
            $res_obj = $tpl_obj->_loadResource(Smarty::CACHE, $tpl_obj->caching_type);
            $timestamp = $exists = false;
            $filepath = $res_obj->buildFilepath($context);
            $res_obj->populateTimestamp($tpl_obj, $filepath, $timestamp, $exists);
            if (!$exists || $timestamp < $context->timestamp) {
                return false;
            }
            $template_class_name = $res_obj->loadTemplateClass($filepath);
            if (class_exists($template_class_name, false)) {
                $template_obj = new $template_class_name($context, $filepath, $timestamp);
                $template_obj->isUpdated = true;
                return $template_obj->isValid;
            }
            return false;
        }
    }
}
