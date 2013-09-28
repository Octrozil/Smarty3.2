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
 * Class for getAutoloadFilters method
 *
 * @package Smarty\Extension
 */
class Smarty_Method_GetAutoloadFilters
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
}
