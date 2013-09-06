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
 * Class for unregisterFilter method
 *
 * @package Smarty\Extension
 */
class Smarty_Extension_UnregisterFilter
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
