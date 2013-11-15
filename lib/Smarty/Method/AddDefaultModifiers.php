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
     * Add default modifiers
     *
     * @api
     * @param Smarty $smarty smarty object
     * @param  array|string $modifiers modifier or list of modifiers to add
     * @return Smarty       current Smarty instance for chaining
     */
    public function addDefaultModifiers(Smarty $smarty, $modifiers)
    {
        if (is_array($modifiers)) {
            $smarty->default_modifiers = array_merge($smarty->default_modifiers, $modifiers);
        } else {
            $smarty->default_modifiers[] = $modifiers;
        }

        return $smarty;
    }
}
