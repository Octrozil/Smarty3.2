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
 * Class for  unregisterResource method
 *
 * @package Smarty\Extension
 */
class Smarty_Extension_UnregisterResource
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
     * Unregisters a resource
     *
     * @api
     * @param  string $type name of resource type
     * @return Smarty
     */
    public function unregisterResource($type)
    {
        if (isset($this->smarty->registered_resources[Smarty::SOURCE][$type])) {
            unset($this->smarty->registered_resources[Smarty::SOURCE][$type]);
        }

        return $this->smarty;
    }
}
