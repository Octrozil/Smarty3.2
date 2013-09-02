<?php

/**
 * Smarty Extension Compiled Plugin
 *
 * Smarty class methods
 *
 *
 * @package CoreExtensions
 * @author Uwe Tews
 */

/**
 * Class for modifier methods
 *
 *
 * @package CoreExtensions
 */
class Smarty_Extension_Compiled
{

    /**
     * Delete compiled template file
     *
     * @api
     * @param  Smarty $smarty   Smarty object
     * @param  string $resource_name template name
     * @param  string $compile_id    compile id
     * @param  integer $exp_time      expiration time
     * @param  string $type     resource type
     * @return integer number of template files deleted
     */
    public function clearCompiledTemplate(Smarty $smarty, $resource_name = null, $compile_id = null, $exp_time = null, $type = null)
    {
        // load compiled resource
        $compiled = Smarty_Resource_Loader::load($smarty, Smarty_Resource_Loader::COMPILED, $type);
        // invalidate complete cache
        Smarty_Resource_Compiled::$resource_cache = array();
        return $compiled->clear($smarty, $resource_name, $compile_id, $exp_time);
    }

    /**
     * Delete compiled config file
     *
     * @api
     * @param  Smarty $smarty   Smarty object
     * @param  string $resource_name template name
     * @param  string $compile_id    compile id
     * @param  integer $exp_time      expiration time
     * @param  string $type     resource type
     * @return integer number of template files deleted
     */
    public function clearCompiledConfig(Smarty $smarty, $resource_name = null, $compile_id = null, $exp_time = null, $type = null)
    {
        // load compiled resource
        $compiled = Smarty_Resource_Loader::load($smarty, Smarty_Resource_Loader::COMPILED, $type);
        // invalidate complete cache
        Smarty_Resource_Compiled::$resource_cache = array();
        return $compiled->clear($smarty, $resource_name, $compile_id, $exp_time, true);
    }
}
