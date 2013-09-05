<?php

/**
 * Smarty Extension Resource Plugin
 *
 * Smarty filter methods
 *
 *
 * @package Smarty
 * @author Uwe Tews
 */

/**
 * Class for filter methods
 *
 *
 * @package Smarty
 */
class Smarty_Extension_Resource
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
     * Registers a resource to fetch a template
     *
     * @api
     * @param  string $type     name of resource type
     * @param  Smarty_Resource_Source|array $callback or instance of Smarty_Resource_Source, or array of callbacks to handle resource (deprecated)
     * @return Smarty
     */
    public function registerResource($type, $callback)
    {
        $this->smarty->registered_resources[Smarty_Resource_Loader::SOURCE][$type] = $callback instanceof Smarty_Resource_Source_File ? $callback : array($callback, false);

        return $this->smarty;
    }

    /**
     * Unregisters a resource
     *
     * @api
     * @param  string $type name of resource type
     * @return Smarty
     */
    public function unregisterResource()
    {
        if (isset($this->smarty->registered_resources[Smarty_Resource_Loader::SOURCE][$type])) {
            unset($this->smarty->registered_resources[Smarty_Resource_Loader::SOURCE][$type]);
        }

        return $this->smarty;
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
        $this->smarty->registered_resources[Smarty_Resource_Loader::CACHE][$type] = $callback instanceof Smarty_Resource_Cache ? $callback : array($callback, false);

        return $this->smarty;
    }

    /**
     * Unregisters a cache resource
     *
     * @api
     * @param  string $type name of cache resource type
     * @return Smarty
     */
    public function unregisterCacheResource($type)
    {
        if (isset($this->smarty->registered_resources[Smarty_Resource_Loader::CACHE][$type])) {
            unset($this->smarty->registered_resources[Smarty_Resource_Loader::CACHE][$type]);
        }

        return $this;
    }
}
