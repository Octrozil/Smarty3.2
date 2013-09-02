<?php

/**
 * Smarty Extension Cache Plugin
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
class Smarty_Extension_Cache
{
    /**
     * Empty cache folder
     *
     * @api
     * @param  Smarty $smarty   Smarty object
     * @param  integer $exp_time expiration time
     * @param  string $type     resource type
     * @return integer number of cache files deleted
     */
    public function clearAllCache(Smarty $smarty, $exp_time = null, $type = null)
    {
        // load cache resource
        $cache = Smarty_Resource_Loader::load($smarty, Smarty_Resource_Loader::COMPILED, $type);
        // invalidate complete cache
        Smarty_Resource_Cache::$resource_cache = array();
        //  call clearAll
        return $cache->clearAll($smarty, $exp_time);
    }

    /**
     * Empty cache for a specific template
     *
     * @api
     * @param  Smarty $smarty   Smarty object
     * @param  string $template_name template name
     * @param  string $cache_id      cache id
     * @param  string $compile_id    compile id
     * @param  integer $exp_time      expiration time
     * @param  string $type          resource type
     * @return integer number of cache files deleted
     */
    public function clearCache(Smarty $smarty, $template_name = null, $cache_id = null, $compile_id = null, $exp_time = null, $type = null)
    {
        // load cache resource and call clear
        $cache = Smarty_Resource_Loader::load($smarty, Smarty_Resource_Loader::COMPILED, $type);
        // invalidate complete cache
        Smarty_Resource_Cache::$resource_cache = array();
        // call clear
        return $cache->clear($smarty, $template_name, $cache_id, $compile_id, $exp_time);
    }
}
