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
 * Class for  unregisterCacheResource method
 *
 * @package Smarty\Extension
 */
class Smarty_Extension_UnregisterCacheResource
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
     * Unregisters a cache resource
     *
     * @api
     * @param  string $type name of cache resource type
     * @return Smarty
     */
    public function unregisterCacheResource($type)
    {
        if (isset($this->smarty->registered_resources[Smarty::CACHE][$type])) {
            unset($this->smarty->registered_resources[Smarty::CACHE][$type]);
        }

        return $this;
    }
}
