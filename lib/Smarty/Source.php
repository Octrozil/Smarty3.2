<?php

/**
 * Smarty Source Object
 *
 * @package Smarty\Resource
 * @author Uwe Tews
 * @author Rodney Rehm
 */

/**
 * Smarty Source Object
 *
 * Storage for Source properties
 *
 * @package Smarty\Resource
 */
class Smarty_Source
{
    /**
     * resource filepath
     *
     * @var string| boolean false
     */
    public $filepath = false;

    /**
     * Resource Timestamp
     * @var integer
     */
    public $timestamp = null;

    /**
     * Resource Existence
     * @var boolean
     */
    public $exists = false;

    /**
     *  Source Resource specific properties
     */

    /**
     * usage of this resource
     * @var mixed
     */
    public $_usage = null;

    /**
     * Template name
     *
     * @var string
     */
    public $name = '';

    /**
     * Resource handler type
     *
     * @var string
     */
    public $type = 'file';

    /**
     * resource UID
     *
     * @var string
     */
    public $uid = '';

    /**
     * array of extends components
     *
     * @var array
     */
    public $components = array();

    /**
     * Object Source Resource handler
     *
     * @var object
     */
    public $handler = null;

    /**
     * Create source object and populate is it source info
     *
     * @param Smarty $smarty smarty object
     * @param string $name   name part of template specification
     * @param string $type   type of source resource handler
     */
    public function __construct($smarty, $name, $type)
    {
        $this->name = $name;
        $this->type = $type;
        // get Resource handler
        if (isset(Smarty::$resource_cache[Smarty::SOURCE][$type])) {
            $this->handler = Smarty::$resource_cache[Smarty::SOURCE][$type];
        } else {
            $this->handler = $smarty->_loadResource(self::SOURCE, $type);
        }
        $this->handler->populate($smarty, $this);
        return $this;
    }
}
