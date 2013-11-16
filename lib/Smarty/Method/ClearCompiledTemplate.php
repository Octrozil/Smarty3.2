<?php

/**
 * Smarty Extension
 * Smarty class methods
 *
 * @package Smarty\Extension
 * @author  Uwe Tews
 */
/**
 * Class for clearCompiledTemplate method
 *
 * @package Smarty\Extension
 */
class Smarty_Method_ClearCompiledTemplate
{
    /**
     * Delete compiled template file
     *
     * @api
     *
     * @param Smarty   $smarty        smarty object
     * @param  string  $resource_name template name
     * @param  string  $compile_id    compile id
     * @param  integer $exp_time      expiration time
     * @param  string  $type          resource type
     *
     * @return integer number of template files deleted
     */
    public function clearCompiledTemplate(Smarty $smarty, $resource_name = null, $compile_id = null, $exp_time = null, $type = null)
    {
        $type = $type ? $type : $smarty->compiled_type;
        // load compiled resource
        $compiled = $smarty->_loadResource(Smarty::COMPILED, $type);
        // invalidate complete cache
        // TODO
        //unset(Smarty::$template_cache[Smarty::COMPILED]);
        return $compiled->clear($smarty, $resource_name, $compile_id, $exp_time, false);
    }
}
