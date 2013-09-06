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
class Smarty_Extension_IsCached
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
     * @param Smarty $this->smarty Smarty object
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
     * @return boolean       cache status
     */
    public function isCached($template = null, $cache_id = null, $compile_id = null, $parent = null)
    {
        {
            if ($this->smarty->force_cache || $this->smarty->force_compile || !($this->smarty->caching == Smarty::CACHING_LIFETIME_CURRENT || $this->smarty->caching == Smarty::CACHING_LIFETIME_SAVED)) {
                // caching is disabled
                return false;
            }
            if ($template === null && ($this->smarty->usage == Smarty::IS_TEMPLATE || $this->smarty->usage == Smarty::IS_CONFIG)) {
                $template = $this->smarty;
            }
            if (is_object($template)) {
                // get source from template clone
                $source = $template->source;
                $tpl_obj = $template;
            } else {
                //get source object from cache  or create new one
                $source = $this->smarty->_load(Smarty::SOURCE, $template);
                $tpl_obj = $this->smarty;
            }
            if ($source->recompiled) {
                // recompiled source can't be cached
                return false;
            }
            $cache = $tpl_obj->_load(Smarty::CACHE, $source, isset($compile_id) ? $compile_id : $tpl_obj->compile_id,
                isset($cache_id) ? $cache_id : $tpl_obj->cache_id, $tpl_obj->caching);
            if (!$cache->exists) {
                return false;
            }
            $cache->loadTemplateClass();
            $template_obj = new $cache->class_name($this->smarty, $parent, $cache->source);
            return $template_obj->isValid;
        }
    }
}
