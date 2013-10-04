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
class Smarty_Method_CompileTemplate
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
     * @param  object $parent     next higher level of Smarty variables
     * @param  null $caching
     * @throws Smarty_Exception_SourceNotFound
     * @throws Exception
     * @return boolean      status of compilation
     */
    public function compileTemplate($template = null, $compile_id = null, $parent = null, $caching = null)
    {
        if ($template === null && ($this->smarty->_usage == Smarty::IS_SMARTY_TPL_CLONE || $this->smarty->_usage == Smarty::IS_CONFIG)) {
            $template = $this->smarty;
        }
        //get source object from cache  or create new one
        $context = Smarty_Context::getContext($this->smarty, $template, null, $compile_id, $parent, false, null, null, null, $caching);
        // checks if source exists
        if (!$context->exists) {
            throw new Smarty_Exception_SourceNotFound($context->type, $context->name);
        }
        if ($context->handler->uncompiled) {
            // uncompiled source returns always true
            return true;
        }
        try {
            $res_obj = $context->smarty->_loadResource(Smarty::COMPILED, $context->smarty->compiled_type);
            $filepath = $res_obj->buildFilepath($context);
            $context->scope = $context->_buildScope($context->smarty, $context->parent);
            $compiler = Smarty_Compiler::load($context, $filepath);
            $compiler->compileTemplateSource();
            $compile_key = isset($context->compile_id) ? $context->compile_id : '';
            $caching_key = (($context->caching) ? 1 : 0);
            unset(Smarty_Context::$_object_cache[Smarty::COMPILED][$context->_key][$compile_key][$caching_key]);
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
