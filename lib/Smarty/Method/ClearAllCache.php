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
 * Class for clearAllCache method
 *
 *
 * @package Smarty\Extension
 */
class Smarty_Method_ClearAllCache
{
    /**
     * Empty cache folder
     *
     * @api
     * @param Smarty | Smarty_Template $object master object
     * @param  integer $exp_time expiration time
     * @param  string $type resource type
     * @return integer number of cache files deleted
     */
    public function clearAllCache(Smarty $smarty, $exp_time = null, $type = null)
    {
        // load cache resource
        $type = $type ? $type : $smarty->caching_type;
        $cache = $smarty->_loadResource(Smarty::CACHE, $type);
        // invalidate complete cache
        // TODO
        //unset(Smarty::$template_cache[Smarty::CACHE]);
        //  call clearAll
        return $cache->clearAll($smarty, $exp_time);
    }
}
