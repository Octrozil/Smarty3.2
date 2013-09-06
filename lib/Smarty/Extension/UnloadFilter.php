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
 * Class for unloadFilter method
 *
 * @package Smarty\Extension
 */
class Smarty_Extension_UnloadFilter
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
}
