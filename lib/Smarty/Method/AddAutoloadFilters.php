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
 * Class for addAutoloadFilters method
 *
 * @package Smarty\Extension
 */
class Smarty_Method_AddAutoloadFilters
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
}
