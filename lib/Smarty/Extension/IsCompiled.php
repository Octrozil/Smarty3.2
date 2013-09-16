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
        if (is_object($template)) {
            // get source from template clone
            $source = $template->source;
            $tpl_obj = $template;
        } else {
            //get source object from cache  or create new one
            $source = $this->smarty->_getSourceObject($template);
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
            $template_obj = $tpl_obj->_getTemplateObject(Smarty::COMPILED, $source, null, isset($compile_id) ? $compile_id : $tpl_obj->compile_id, null, isset($caching) ? $caching : $tpl_obj->caching, true);
            if ($template_obj === false || $template_obj->timestamp < $source->timestamp) {
                return false;
            }
            return  $template_obj->isValid;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
