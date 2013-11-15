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
     * Add autoload filters
     *
     * @api
     * @param Smarty $smarty smarty object
     * @param  array $filters filters to load automatically
     * @param  string $type "pre", "output", … specify the filter type to set. Defaults to none treating $filters' keys as the appropriate types
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
}
