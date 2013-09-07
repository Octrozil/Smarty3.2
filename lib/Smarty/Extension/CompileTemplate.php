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
 * Class for compileTemplate method
 *
 * @package Smarty\Extension
 */
class Smarty_Extension_CompileTemplate
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
    public function compileTemplate($template = null, $compile_id = null, $parent = null)
    {
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
            if (!$source->exists) {
                throw new Smarty_Exception_SourceNotFound($source->type, $source->name);
            }
            $tpl_obj = $this->smarty;
        }
        if ($source->uncompiled) {
            // uncompiled source returns always false
            return true;
        }
        try {
            $compiled = $tpl_obj->_load(Smarty::COMPILED, $source, isset($compile_id) ? $compile_id : $tpl_obj->compile_id,
                $tpl_obj->caching);
            if ($tpl_obj->debugging) {
                Smarty_Debug::start_compile($source);
            }
            $compiler = Smarty_Compiler::load($tpl_obj, $compiled->source, $compiled->caching);
            $compiler->compileTemplateSource($compiled);
            $compiled->populateTimestamp($tpl_obj);
            unset($compiler);
            if ($tpl_obj->debugging) {
                Smarty_Debug::end_compile($source);
            }
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }
}