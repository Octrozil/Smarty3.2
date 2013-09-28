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
 * Class for registerPlugin method
 *
 * @package Smarty\Extension
 */
class Smarty_Method_RegisterPlugin
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
        if (isset($this->smarty->_registered['plugin'][$type][$tag])) {
            throw new Smarty_Exception("registerPlugin(): Plugin tag \"{$tag}\" already registered");
        } elseif (!is_callable($callback)) {
            throw new Smarty_Exception("registerPlugin(): Plugin \"{$tag}\" not callable");
        } else {
            if (is_object($callback)) {
                $callback = array($callback, '__invoke');
            }
            $this->smarty->_registered['plugin'][$type][$tag] = array($callback, (bool)$cacheable, (array)$cache_attr);
        }

        return $this->smarty;
    }
}
