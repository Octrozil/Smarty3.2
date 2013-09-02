<?php

/**
 * Smarty Extension Filter Plugin
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
class Smarty_Extension_Filter
{

    /**
     * Registers a filter function
     *
     * @api
     * @param  Smarty $smarty   Smarty object
     * @param  string $type     filter type
     * @param  callback $callback
     * @throws Smarty_Exception
     * @return Smarty
     */
    public function registerFilter(Smarty $smarty, $type, $callback)
    {
        if (!in_array($type, array('pre', 'post', 'output', 'variable'))) {
            throw new Smarty_Exception("registerFilter(): Invalid filter type \"{$type}\"");
        }
        if (is_callable($callback)) {
            if ($callback instanceof Closure) {
                $smarty->registered_filters[$type][] = $callback;
            } else {
                if (is_object($callback)) {
                    $callback = array($callback, '__invoke');
                }
                $smarty->registered_filters[$type][$this->_getFilterName($callback)] = $callback;
            }
        } else {
            throw new Smarty_Exception("registerFilter(): Invalid callback");
        }

        return $smarty;
    }

    /**
     * Unregisters a filter function
     *
     * @api
     * @param  Smarty $smarty   Smarty object
     * @param  string $type     filter type
     * @param  callback $callback
     * @return Smarty
     */
    public function unregisterFilter(Smarty $smarty, $type, $callback)
    {
        if (!isset($smarty->registered_filters[$type])) {
            return $smarty;
        }
        if ($callback instanceof Closure) {
            foreach ($smarty->registered_filters[$type] as $key => $_callback) {
                if ($callback === $_callback) {
                    unset($smarty->registered_filters[$type][$key]);

                    return $smarty;
                }
            }
        } else {
            if (is_object($callback)) {
                $callback = array($callback, '__invoke');
            }
            $name = $this->_getFilterName($callback);
            if (isset($smarty->registered_filters[$type][$name])) {
                unset($smarty->registered_filters[$type][$name]);
            }
        }

        return $smarty;
    }

    /**
     * load a filter of specified type and name
     *
     * @api
     * @param  Smarty $smarty   Smarty object
     * @param  string $type filter type
     * @param  string $name filter name
     * @throws Smarty_Exception
     * @return bool
     */
    public function loadFilter(Smarty $smarty, $type, $name)
    {
        if (!in_array($type, array('pre', 'post', 'output', 'variable'))) {
            throw new Smarty_Exception("loadFilter(): Invalid filter type \"{$type}\"");
        }
        $_plugin = "smarty_{$type}filter_{$name}";
        $_filter_name = $_plugin;
        if ($smarty->_loadPlugin($_plugin)) {
            if (class_exists($_plugin, false)) {
                $_plugin = array($_plugin, 'execute');
            }
            if (is_callable($_plugin)) {
                $smarty->registered_filters[$type][$_filter_name] = $_plugin;
                return true;
            }
        }
        throw new Smarty_Exception("loadFilter(): {$type}filter \"{$name}\" not callable");
    }

    /**
     * unload a filter of specified type and name
     *
     * @api
     * @param  Smarty $smarty   Smarty object
     * @param  string $type filter type
     * @param  string $name filter name
     * @return Smarty
     */
    public function unloadFilter(Smarty $smarty, $type, $name)
    {
        $_filter_name = "smarty_{$type}filter_{$name}";
        if (isset($smarty->registered_filters[$type][$_filter_name])) {
            unset($smarty->registered_filters[$type][$_filter_name]);
        }

        return $smarty;
    }

    /**
     * Set autoload filters
     *
     * @param  Smarty $smarty   Smarty object
     * @param  array $filters filters to load automatically
     * @param  string $type    "pre", "output", … specify the filter type to set. Defaults to none treating $filters' keys as the appropriate types
     * @return Smarty current Smarty instance for chaining
     */
    public function setAutoloadFilters(Smarty $smarty, $filters, $type = null)
    {
        if ($type !== null) {
            $smarty->autoload_filters[$type] = (array)$filters;
        } else {
            $smarty->autoload_filters = (array)$filters;
        }

        return $smarty;
    }

    /**
     * Add autoload filters
     *
     * @api
     * @param  Smarty $smarty   Smarty object
     * @param  array $filters filters to load automatically
     * @param  string $type    "pre", "output", … specify the filter type to set. Defaults to none treating $filters' keys as the appropriate types
     * @return Smarty current Smarty instance for chaining
     */
    public function addAutoloadFilters(Smarty $smarty, $filters, $type = null)
    {
        if ($type !== null) {
            if (!empty($smarty->autoload_filters[$type])) {
                $smarty->autoload_filters[$type] = array_merge($smarty->autoload_filters[$type], (array)$filters);
            } else {
                $smarty->autoload_filters[$type] = (array)$filters;
            }
        } else {
            foreach ((array)$filters as $key => $value) {
                if (!empty($smarty->autoload_filters[$key])) {
                    $smarty->autoload_filters[$key] = array_merge($smarty->autoload_filters[$key], (array)$value);
                } else {
                    $smarty->autoload_filters[$key] = (array)$value;
                }
            }
        }

        return $smarty;
    }

    /**
     * Get autoload filters
     *
     * @api
     * @param  Smarty $smarty   Smarty object
     * @param  string $type type of filter to get autoloads for. Defaults to all autoload filters
     * @return array  array( 'type1' => array( 'filter1', 'filter2', … ) ) or array( 'filter1', 'filter2', …) if $type was specified
     */
    public function getAutoloadFilters(Smarty $smarty, $type = null)
    {
        if ($type !== null) {
            return isset($smarty->autoload_filters[$type]) ? $smarty->autoload_filters[$type] : array();
        }

        return $smarty->autoload_filters;
    }

    /**
     * Run filters over content
     *
     * The filters will be lazy loaded if required
     * class name format: Smarty_FilterType_FilterName
     * plugin filename format: filtertype.filtername.php
     * Smarty2 filter plugins could be used
     *
     * @param  Smarty $smarty   Smarty object
     * @param  string $type    the type of filter ('pre','post','output') which shall run
     * @param  string $content the content which shall be processed by the filters
     * @throws Smarty_Exception
     * @return string           the filtered content
     */
    public function runFilter(Smarty $smarty, $type, $content)
    {
        $output = $content;
        // loop over autoload filters of specified type
        if (!empty($smarty->autoload_filters[$type])) {
            foreach ((array)$smarty->autoload_filters[$type] as $name) {
                $plugin_name = "Smarty_{$type}filter_{$name}";
                if ($smarty->_loadPlugin($plugin_name)) {
                    if (function_exists($plugin_name)) {
                        // use loaded Smarty2 style plugin
                        $output = $plugin_name($output, $smarty);
                    } elseif (class_exists($plugin_name, false)) {
                        // loaded class of filter plugin
                        $output = call_user_func(array($plugin_name, 'execute'), $output, $smarty);
                    }
                } else {
                    // nothing found, throw exception
                    throw new Smarty_Exception("Unable to load filter {$plugin_name}");
                }
            }
        }
        // loop over registered filters of specified type
        if (!empty($smarty->registered_filters[$type])) {
            foreach ($smarty->registered_filters[$type] as $key => $name) {
                if (is_array($smarty->registered_filters[$type][$key])) {
                    $output = call_user_func($smarty->registered_filters[$type][$key], $output, $smarty);
                } else {
                    $output = $smarty->registered_filters[$type][$key]($output, $smarty);
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
