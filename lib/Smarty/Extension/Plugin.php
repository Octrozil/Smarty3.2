<?php

/**
 * Smarty Extension Plugin Plugin
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
class Smarty_Extension_Plugin
{
    /**
     * Registers plugin to be used in templates
     *
     * @param  Smarty $smarty   Smarty object
     * @param  string $type       plugin type
     * @param  string $tag        name of template tag
     * @param  callback $callback   PHP callback to register
     * @param  boolean $cacheable  if true (default) this fuction is cachable
     * @param  array $cache_attr caching attributes if any
     * @return Smarty
     * @throws Smarty_Exception when the plugin tag is invalid
     */
    public function registerPlugin(Smarty $smarty, $type, $tag, $callback, $cacheable = true, $cache_attr = null)
    {
        if (isset($smarty->registered_plugins[$type][$tag])) {
            throw new Smarty_Exception("registerPlugin(): Plugin tag \"{$tag}\" already registered");
        } elseif (!is_callable($callback)) {
            throw new Smarty_Exception("registerPlugin(): Plugin \"{$tag}\" not callable");
        } else {
            if (is_object($callback)) {
                $callback = array($callback, '__invoke');
            }
            $smarty->registered_plugins[$type][$tag] = array($callback, (bool)$cacheable, (array)$cache_attr);
        }

        return $smarty;
    }

    /**
     * Unregister Plugin
     *
     * @api
     * @param  Smarty $smarty   Smarty object
     * @param  string $type of plugin
     * @param  string $tag  name of plugin
     * @return Smarty
     */
    public function unregisterPlugin(Smarty $smarty, $type, $tag)
    {
        if (isset($smarty->registered_plugins[$type][$tag])) {
            unset($smarty->registered_plugins[$type][$tag]);
        }

        return $smarty;
    }

    /**
     * Registers a default plugin handler
     *
     * @api
     * @param  Smarty $smarty   Smarty object
     * @param  callable $callback class/method name
     * @return Smarty
     * @throws Smarty_Exception if $callback is not callable
     */
    public function registerDefaultPluginHandler(Smarty $smarty, $callback)
    {
        if (is_callable($callback)) {
            $smarty->default_plugin_handler_func = $callback;
        } else {
            throw new Smarty_Exception("registerDefaultPluginHandler(): Invalid callback");
        }

        return $smarty;
    }

    /**
     * Unregisters a default plugin handler
     *
     * @api
     * @param  Smarty $smarty   Smarty object
     * @return Smarty
     */
    public function unregisterDefaultPluginHandler(Smarty $smarty)
    {
        $smarty->default_plugin_handler_func = null;

        return $smarty;
    }
}
