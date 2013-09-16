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
 * Class for registerResource method
 *
 * @package Smarty\Extension
 */
class Smarty_Extension_RegisterResource
{

    /**
     *  Smarty object
     *
     * @var Smarty
     */
    public $smarty;

    /**
     * registered resources
     * @var array
     * @internal
     */
    public $registered_resources = array();

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
     * Registers a resource for source templates
     *
     * @api
     * @param  string                         $type     name of resource type
     * @param  Smarty_Resource_Source|array   $callback or instance of Smarty_Resource_Source, or array of callbacks to handle resource (deprecated)
     * @return Smarty
     */
    public function registerResource($type, $callback)
    {
        $this->registered_resources[Smarty::SOURCE][$type] = $callback instanceof Smarty_Resource_Source_File ? $callback : array($callback, false);
        return $this->smarty;
    }
}
