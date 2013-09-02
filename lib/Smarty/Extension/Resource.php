<?php

/**
 * Smarty Extension Resource Plugin
 *
 * Smarty filter methods
 *
 *
 * @package CoreExtensions
 * @author Uwe Tews
 */

/**
 * Class for filter methods
 *
 *
 * @package CoreExtensions
 */
class Smarty_Extension_Resource
{
    /**
     * Registers a resource to fetch a template
     *
     * @api
     * @param  Smarty $smarty   Smarty object
     * @param  string $type     name of resource type
     * @param  Smarty_Resource_Source|array $callback or instance of Smarty_Resource_Source, or array of callbacks to handle resource (deprecated)
     * @return Smarty
     */
    public function registerResource(Smarty $smarty, $type, $callback)
    {
        $smarty->registered_resources[self::SOURCE][$type] = $callback instanceof Smarty_Resource_Source ? $callback : array($callback, false);

        return $smarty;
    }

    /**
     * Unregisters a resource
     *
     * @api
     * @param  Smarty $smarty   Smarty object
     * @param  string $type name of resource type
     * @return Smarty
     */
    public function unregisterResource(Smarty $smarty, $type)
    {
        if (isset($smarty->registered_resources[self::SOURCE][$type])) {
            unset($smarty->registered_resources[self::SOURCE][$type]);
        }

        return $smarty;
    }

    /**
     * Registers a cache resource to cache a template's output
     *
     * @api
     * @param  Smarty $smarty   Smarty object
     * @param  string $type     name of cache resource type
     * @param  Smarty_Cache_Resource $callback instance of Smarty_Cache_Resource to handle output caching
     * @return Smarty
     */
    public function registerCacheResource(Smarty $smarty, $type, $callback)
    {
        $smarty->registered_resources[self::CACHE][$type] = $callback instanceof Smarty_Cache_Resource ? $callback : array($callback, false);

        return $smarty;
    }

    /**
     * Unregisters a cache resource
     *
     * @api
     * @param  Smarty $smarty   Smarty object
     * @param  string $type name of cache resource type
     * @return Smarty
     */
    public function unregisterCacheResource(Smarty $smarty, $type)
    {
        if (isset($smarty->registered_resources[self::CACHE][$type])) {
            unset($smarty->registered_resources[self::CACHE][$type]);
        }

        return $smarty;
    }

}
