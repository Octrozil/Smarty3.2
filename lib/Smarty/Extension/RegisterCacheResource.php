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
 * Class for registerCacheResource method
 *
 * @package Smarty\Extension
 */
class Smarty_Extension_RegisterCacheResource
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
     * Registers a cache resource to cache a template's output
     *
     * @api
     * @param  string $type     name of cache resource type
     * @param  Smarty_Resource_Cache $callback instance of Smarty_Resource_Cache to handle output caching
     * @return Smarty
     */
    public function registerCacheResource($type, $callback)
    {
        $this->smarty->registered_resources[Smarty::CACHE][$type] = $callback instanceof Smarty_Resource_Cache ? $callback : array($callback, false);

        return $this->smarty;
    }
}
