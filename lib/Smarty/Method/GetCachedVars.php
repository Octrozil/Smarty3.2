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
 * Class for getCachedVars method
 *
 * @package Smarty\Extension
 */
class Smarty_Method_GetCachedVars
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
     * Get value from persistent cache storage
     *
     * @api
     * @param  string $key key of value to retrieve, null for all values (default)
     * @return mixed  value or array of values
     */
    public function getCachedVars($key = null)
    {
        if (!$this->smarty->rootTemplate) {
            $this->smarty->findRootTemplate();
        }

        if ($key === null) {
            return isset($this->smarty->rootTemplate->properties['cachedValues']) ? $this->smarty->rootTemplate->properties['cachedValues'] : array();
        }

        return isset($this->smarty->rootTemplate->properties['cachedValues'][$key]) ? $this->smarty->rootTemplate->properties['cachedValues'][$key] : null;
    }
}
