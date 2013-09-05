<?php

/**
 * Smarty Extension Plugin Plugin
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
class Smarty_Extension_Plugin
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
     * Registers plugin to be used in templates
     *
     * @param  string $type       plugin type
     * @param  string $tag        name of template tag
     * @param  callback $callback   PHP callback to register
     * @param  boolean $cacheable  if true (default) this fuction is cachable
     * @param  array $cache_attr caching attributes if any
     * @return Smarty
     * @throws Smarty_Exception when the plugin tag is invalid
     */
    public function registerPlugin($type, $tag, $callback, $cacheable = true, $cache_attr = null)
    {
        if (isset($this->smarty->registered_plugins[$type][$tag])) {
            throw new Smarty_Exception("registerPlugin(): Plugin tag \"{$tag}\" already registered");
        } elseif (!is_callable($callback)) {
            throw new Smarty_Exception("registerPlugin(): Plugin \"{$tag}\" not callable");
        } else {
            if (is_object($callback)) {
                $callback = array($callback, '__invoke');
            }
            $this->smarty->registered_plugins[$type][$tag] = array($callback, (bool)$cacheable, (array)$cache_attr);
        }

        return $this->smarty;
    }

    /**
     * Unregister Plugin
     *
     * @api
     * @param  string $type of plugin
     * @param  string $tag  name of plugin
     * @return Smarty
     */
    public function unregisterPlugin($type, $tag)
    {
        if (isset($this->smarty->registered_plugins[$type][$tag])) {
            unset($this->smarty->registered_plugins[$type][$tag]);
        }

        return $this->smarty;
    }

    /**
     * Registers a default plugin handler
     *
     * @api
     * @param  callable $callback class/method name
     * @return Smarty
     * @throws Smarty_Exception if $callback is not callable
     */
    public function registerDefaultPluginHandler($callback)
    {
        if (is_callable($callback)) {
            $this->smarty->default_plugin_handler_func = $callback;
        } else {
            throw new Smarty_Exception("registerDefaultPluginHandler(): Invalid callback");
        }

        return $this->smarty;
    }

    /**
     * Unregisters a default plugin handler
     *
     * @api
     * @return Smarty
     */
    public function unregisterDefaultPluginHandler()
    {
        $this->smarty->default_plugin_handler_func = null;

        return $this->smarty;
    }
}
