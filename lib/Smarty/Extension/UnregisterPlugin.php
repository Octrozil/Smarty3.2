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
 * Class for unregisterPlugin method
 *
 * @package Smarty\Extension
 */
class Smarty_Extension_UnregisterPlugin
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
     * Unregister Plugin
     *
     * @api
     * @param  string $type of plugin
     * @param  string $tag  name of plugin
     * @return Smarty
     */
    public function unregisterPlugin($type, $tag)
    {
        if (isset($this->smarty->registered_plugins[$type][$tag])) {
            unset($this->smarty->registered_plugins[$type][$tag]);
        }

        return $this->smarty;
    }
}
