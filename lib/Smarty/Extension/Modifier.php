<?php

/**
 * Smarty Extension Modifier Plugin
 *
 * Smarty filter methods
 *
 *
 * @package Smarty
 * @author Uwe Tews
 */

/**
 * Class for modifier methods
 *
 *
 * @package Smarty
 */
class Smarty_Extension_Modifier
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
     * Set default modifiers
     *
     * @api
     * @param  array|string $modifiers modifier or list of modifiers to set
     * @return Smarty       current Smarty instance for chaining
     */
    public function setDefaultModifiers($modifiers)
    {
        $this->smarty->default_modifiers = (array)$modifiers;

        return $this->smarty;
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
