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
class Smarty_Extension_IsCompiled
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
     * test if compiled template is valid
     *
     * @api
     * @param  string|object $template   the resource handle of the template file or template object
     * @param  mixed $compile_id compile id to be used with this template
     * @param  object $parent     next higher level of Smarty variables
     * @return boolean       cache status
     */
    public function isCompiled($template = null, $compile_id = null, $parent = null)
    {
        if ($this->smarty->force_compile) {
            return false;
        }
        if ($template === null && ($this->smarty->_usage == Smarty::IS_SMARTY_TPL_CLONE || $this->smarty->_usage == Smarty::IS_CONFIG)) {
            $template = $this->smarty;
        }
        if (is_object($template)) {
            // get source from template clone
            $source = $template->source;
            $tpl_obj = $template;
        } else {
            //get source object from cache  or create new one
            $source = $this->smarty->_load(Smarty::SOURCE, $template);
            if (!$source->exists) {
                throw new Smarty_Exception_SourceNotFound($source->type, $source->name);
            }
            $tpl_obj = $this->smarty;
        }
        if ($source->recompiled) {
            // recompiled source returns always false
            return false;
        }
        if ($source->uncompiled) {
            // uncompiled source returns always false
            return true;
        }
        try {
            $compiled = $tpl_obj->_load(Smarty::COMPILED, $source, isset($compile_id) ? $compile_id : $tpl_obj->compile_id,
                $tpl_obj->caching);
            if (!$compiled->exists || $compiled->timestamp < $source->timestamp) {
                return false;
            }
            $compiled->loadTemplateClass();
            if (class_exists($compiled->template_class_name, false)) {
                $template_obj =  new $compiled->template_class_name($this->smarty, $parent, $compiled->source);
                return  $template_obj->isValid;
            }
            return false;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
