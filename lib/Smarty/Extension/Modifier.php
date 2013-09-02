<?php

/**
 * Smarty Extension Modifier Plugin
 *
 * Smarty filter methods
 *
 *
 * @package CoreExtensions
 * @author Uwe Tews
 */

/**
 * Class for modifier methods
 *
 *
 * @package CoreExtensions
 */
class Smarty_Extension_Modifier
{

    /**
     * Set default modifiers
     *
     * @api
     * @param  Smarty $smarty   Smarty object
     * @param  array|string $modifiers modifier or list of modifiers to set
     * @return Smarty       current Smarty instance for chaining
     */
    public function setDefaultModifiers(Smarty $smarty, $modifiers)
    {
        $smarty->default_modifiers = (array)$modifiers;

        return $smarty;
    }

    /**
     * Add default modifiers
     *
     * @api
     * @param  Smarty $smarty   Smarty object
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

    /**
     * Get default modifiers
     *
     * @api
     * @param  Smarty $smarty   Smarty object
     * @return array list of default modifiers
     */
    public function getDefaultModifiers(Smarty $smarty)
    {
        return $smarty->default_modifiers;
    }
}
