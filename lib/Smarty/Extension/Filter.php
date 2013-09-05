<?php

/**
 * Smarty Extension Filter Plugin
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
class Smarty_Extension_Filter
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
     * Registers a filter function
     *
     * @api
     * @param  string $type     filter type
     * @param  callback $callback
     * @throws Smarty_Exception
     * @return Smarty
     */
    public function registerFilter($type, $callback)
    {
        if (!in_array($type, array('pre', 'post', 'output', 'variable'))) {
            throw new Smarty_Exception("registerFilter(): Invalid filter type \"{$type}\"");
        }
        if (is_callable($callback)) {
            if ($callback instanceof Closure) {
                $this->smarty->registered_filters[$type][] = $callback;
            } else {
                if (is_object($callback)) {
                    $callback = array($callback, '__invoke');
                }
                $this->smarty->registered_filters[$type][$this->_getFilterName($callback)] = $callback;
            }
        } else {
            throw new Smarty_Exception("registerFilter(): Invalid callback");
        }

        return $this->smarty;
    }

    /**
     * Unregisters a filter function
     *
     * @api
     * @param  string $type     filter type
     * @param  callback $callback
     * @return Smarty
     */
    public function unregisterFilter($type, $callback)
    {
        if (!isset($this->smarty->registered_filters[$type])) {
            return $this->smarty;
        }
        if ($callback instanceof Closure) {
            foreach ($this->smarty->registered_filters[$type] as $key => $_callback) {
                if ($callback === $_callback) {
                    unset($this->smarty->registered_filters[$type][$key]);

                    return $this->smarty;
                }
            }
        } else {
            if (is_object($callback)) {
                $callback = array($callback, '__invoke');
            }
            $name = $this->_getFilterName($callback);
            if (isset($this->smarty->registered_filters[$type][$name])) {
                unset($this->smarty->registered_filters[$type][$name]);
            }
        }

        return $this->smarty;
    }

    /**
     * load a filter of specified type and name
     *
     * @api
     * @param  string $type filter type
     * @param  string $name filter name
     * @throws Smarty_Exception
     * @return bool
     */
    public function loadFilter($type, $name)
    {
        if (!in_array($type, array('pre', 'post', 'output', 'variable'))) {
            throw new Smarty_Exception("loadFilter(): Invalid filter type \"{$type}\"");
        }
        $_plugin = "smarty_{$type}filter_{$name}";
        $_filter_name = $_plugin;
        if ($this->smarty->_loadPlugin($_plugin)) {
            if (class_exists($_plugin, false)) {
                $_plugin = array($_plugin, 'execute');
            }
            if (is_callable($_plugin)) {
                $this->smarty->registered_filters[$type][$_filter_name] = $_plugin;
                return true;
            }
        }
        throw new Smarty_Exception("loadFilter(): {$type}filter \"{$name}\" not callable");
    }

    /**
     * unload a filter of specified type and name
     *
     * @api
     * @param  string $type filter type
     * @param  string $name filter name
     * @return Smarty
     */
    public function unloadFilter($type, $name)
    {
        $_filter_name = "smarty_{$type}filter_{$name}";
        if (isset($this->smarty->registered_filters[$type][$_filter_name])) {
            unset($this->smarty->registered_filters[$type][$_filter_name]);
        }

        return $this->smarty;
    }

    /**
     * Set autoload filters
     *
     * @param  array $filters filters to load automatically
     * @param  string $type    "pre", "output", … specify the filter type to set. Defaults to none treating $filters' keys as the appropriate types
     * @return Smarty current Smarty instance for chaining
     */
    public function setAutoloadFilters($filters, $type = null)
    {
        if ($type !== null) {
            $this->smarty->autoload_filters[$type] = (array)$filters;
        } else {
            $this->smarty->autoload_filters = (array)$filters;
        }

        return $this->smarty;
    }

    /**
     * Add autoload filters
     *
     * @api
     * @param  array $filters filters to load automatically
     * @param  string $type    "pre", "output", … specify the filter type to set. Defaults to none treating $filters' keys as the appropriate types
     * @return Smarty current Smarty instance for chaining
     */
    public function addAutoloadFilters($filters, $type = null)
    {
        if ($type !== null) {
            if (!empty($this->smarty->autoload_filters[$type])) {
                $this->smarty->autoload_filters[$type] = array_merge($this->smarty->autoload_filters[$type], (array)$filters);
            } else {
                $this->smarty->autoload_filters[$type] = (array)$filters;
            }
        } else {
            foreach ((array)$filters as $key => $value) {
                if (!empty($this->smarty->autoload_filters[$key])) {
                    $this->smarty->autoload_filters[$key] = array_merge($this->smarty->autoload_filters[$key], (array)$value);
                } else {
                    $this->smarty->autoload_filters[$key] = (array)$value;
                }
            }
        }

        return $this->smarty;
    }

    /**
     * Get autoload filters
     *
     * @api
     * @param  string $type type of filter to get autoloads for. Defaults to all autoload filters
     * @return array  array( 'type1' => array( 'filter1', 'filter2', … ) ) or array( 'filter1', 'filter2', …) if $type was specified
     */
    public function getAutoloadFilters($type = null)
    {
        if ($type !== null) {
            return isset($this->smarty->autoload_filters[$type]) ? $this->smarty->autoload_filters[$type] : array();
        }

        return $this->smarty->autoload_filters;
    }

    /**
     * Run filters over content
     *
     * The filters will be lazy loaded if required
     * class name format: Smarty_FilterType_FilterName
     * plugin filename format: filtertype.filtername.php
     * Smarty2 filter plugins could be used
     *
     * @internal
     * @param  string $type    the type of filter ('pre','post','output') which shall run
     * @param  string $content the content which shall be processed by the filters
     * @throws Smarty_Exception
     * @return string           the filtered content
     */
    public function _runFilter($type, $content)
    {
        $output = $content;
        // loop over autoload filters of specified type
        if (!empty($this->smarty->autoload_filters[$type])) {
            foreach ((array)$this->smarty->autoload_filters[$type] as $name) {
                $plugin_name = "Smarty_{$type}filter_{$name}";
                if ($this->smarty->_loadPlugin($plugin_name)) {
                    if (function_exists($plugin_name)) {
                        // use loaded Smarty2 style plugin
                        $output = $plugin_name($output, $this->smarty);
                    } elseif (class_exists($plugin_name, false)) {
                        // loaded class of filter plugin
                        $output = call_user_func(array($plugin_name, 'execute'), $output, $this->smarty);
                    }
                } else {
                    // nothing found, throw exception
                    throw new Smarty_Exception("Unable to load filter {$plugin_name}");
                }
            }
        }
        // loop over registered filters of specified type
        if (!empty($this->smarty->registered_filters[$type])) {
            foreach ($this->smarty->registered_filters[$type] as $key => $name) {
                if (is_array($this->smarty->registered_filters[$type][$key])) {
                    $output = call_user_func($this->smarty->registered_filters[$type][$key], $output, $this->smarty);
                } else {
                    $output = $this->smarty->registered_filters[$type][$key]($output, $this->smarty);
                }
            }
        }
        // return filtered output
        return $output;
    }

    /**
     * Return internal filter name
     *
     * @internal
     * @param  callback $function_name
     * @return string
     */
    public function _getFilterName($function_name)
    {
        if (is_array($function_name)) {
            $_class_name = (is_object($function_name[0]) ?
                get_class($function_name[0]) : $function_name[0]);

            return $_class_name . '_' . $function_name[1];
        } else {
            return $function_name;
        }
    }
}
