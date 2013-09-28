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
class Smarty_Method_UnregisterCacheResource
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
        if (isset($this->smarty->_registered['resource'][Smarty::CACHE][$type])) {
            unset($this->smarty->_registered['resource'][Smarty::CACHE][$type]);
        }

        return $this;
    }
}
