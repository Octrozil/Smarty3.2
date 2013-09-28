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
 * Class for addDefaultModifiers method
 *
 * @package Smarty\Extension
 */
class Smarty_Method_AddDefaultModifiers
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
     * Add default modifiers
     *
     * @api
     * @param  array|string $modifiers modifier or list of modifiers to add
     * @return Smarty       current Smarty instance for chaining
     */
    public function addDefaultModifiers($modifiers)
    {
        if (is_array($modifiers)) {
            $this->smarty->default_modifiers = array_merge($this->smarty->default_modifiers, $modifiers);
        } else {
            $this->smarty->default_modifiers[] = $modifiers;
        }

        return $this->smarty;
    }
}
