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
class Smarty_Extension_ClearAllCache
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
        $cache = $this->smarty->_loadResource(Smarty::CACHE, $type);
        // invalidate complete cache
        // TODO
        //unset(Smarty::$template_cache[Smarty::CACHE]);
        //  call clearAll
        return $cache->clearAll($this->smarty, $exp_time);
    }
}
