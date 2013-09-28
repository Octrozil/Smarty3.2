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
 * Class for getDefaultModifiers method
 *
 * @package Smarty\Extension
 */
class Smarty_Method_GetDefaultModifiers
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
     * Get default modifiers
     *
     * @api
     * @return array list of default modifiers
     */
    public function getDefaultModifiers()
    {
        return $this->smarty->default_modifiers;
    }
}
