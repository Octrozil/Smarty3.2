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
 * Class for assignCached method
 *
 * @package Smarty\Extension
 */
class Smarty_Method_AssignCached
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
     * Save value to persistent cache storage
     *
     * @api
     * @param  string|array $key   key to store data under, or array of key => values to store
     * @param  mixed $value value to store for $key, ignored if key is an array
     * @return Smarty       $this for chaining
     */
    public function assignCached($key, $value = null)
    {
        if (!$this->smarty->rootTemplate) {
            $this->smarty->findRootTemplate();
        }

        if (is_array($key)) {
            foreach ($key as $_key => $_value) {
                if ($_key !== '') {
                    $this->smarty->rootTemplate->properties['cachedValues'][$_key] = $_value;
                }
            }
        } else {
            if ($key !== '') {
                $this->smarty->rootTemplate->properties['cachedValues'][$key] = $value;
            }
        }

        return $this->smarty;
    }
}
