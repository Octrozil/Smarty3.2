<?php

/**
 * Smarty Extension Cache Plugin
 *
 * Smarty class methods
 *
 *
 * @package Smarty
 * @author Uwe Tews
 */

/**
 * Class for modifier methods
 *
 *
 * @package Smarty
 */
class Smarty_Extension_Cache
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
     * Empty cache folder
     *
     * @api
     * @param  integer $exp_time expiration time
     * @param  string $type     resource type
     * @return integer number of cache files deleted
     */
    public function clearAllCache($exp_time = null, $type = null)
    {
        // load cache resource
        $type = $type ? $type : $this->smarty->caching_type;
        $cache = $this->smarty->_load(Smarty::CACHE, null, $type);
        // invalidate complete cache
        unset(Smarty::$resource_cache[Smarty::CACHE]);
        //  call clearAll
        return $cache->clearAll($this->smarty, $exp_time);
    }

    /**
     * Empty cache for a specific template
     *
     * @api
     * @param  string $template_name template name
     * @param  string $cache_id      cache id
     * @param  string $compile_id    compile id
     * @param  integer $exp_time      expiration time
     * @param  string $type          resource type
     * @return integer number of cache files deleted
     */
    public function clearCache($template_name = null, $cache_id = null, $compile_id = null, $exp_time = null, $type = null)
    {
        // load cache resource and call clear
        $type = $type ? $type : $this->smarty->caching_type;
        $cache = $this->smarty->_load(Smarty::CACHE, null, $type);
        // invalidate complete cache
        // invalidate complete cache
        unset(Smarty::$resource_cache[Smarty::CACHE]);
        // call clear
        return $cache->clear($this->smarty, $template_name, $cache_id, $compile_id, $exp_time);
    }
}
