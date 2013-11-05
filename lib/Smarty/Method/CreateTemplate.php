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
 * Class for createTemplate method
 *
 * @package Smarty\Extension
 */
class Smarty_Method_CreateTemplate
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
     * creates a template object
     *
     * @api
     * @param  string $template_resource the resource handle of the template file
     * @param  mixed $cache_id          cache id to be used with this template
     * @param  mixed $compile_id        compile id to be used with this template
     * @param  object $parent            next higher level of Smarty variables
     * @throws Smarty_Exception
     * @return Smarty           template object
     */
    public function createTemplate($template_resource, $cache_id = null, $compile_id = null, $parent = null)
    {
        if (!empty($cache_id) && (is_object($cache_id) || is_array($cache_id))) {
            $parent = $cache_id;
            $cache_id = null;
        }
        if (!empty($parent) && is_array($parent)) {
            $data = $parent;
            $parent = null;
        } else {
            $data = null;
        }
        $tpl_obj = clone $this->smarty;
        $tpl_obj->_usage = Smarty::IS_SMARTY_TPL_CLONE;
        $tpl_obj->parent = $parent;
        if (isset($cache_id)) {
            $tpl_obj->cache_id = $cache_id;
        }
        if (isset($compile_id)) {
            $tpl_obj->compile_id = $compile_id;
        }
        //get context object from cache  or create new one
        $context = $tpl_obj->_getContext($template_resource);
        // checks if source exists
        if (!$context->exists) {
            throw new Smarty_Exception_SourceNotFound($context->type, $context->name);
        }
        $tpl_obj->source = $context;
        $tpl_obj->_tpl_vars = new Smarty_Variable_Scope();
        if (isset($data)) {
            foreach ($data as $varname => $value) {
                $tpl_obj->_tpl_vars->$varname = new Smarty_Variable($value);
            }
        }
        return $tpl_obj;
    }
}
