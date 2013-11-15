<?php

/**
 * Smarty Context Object
 *
 * @package Smarty\Core
 * @author Uwe Tews
 */

/**
 * Smarty Context Object
 *
 * Storage for Source and Context properties
 *
 * @package Smarty\Core
 */
class Smarty_Context //extends Smarty_Exception_Magic
{
    /**
     * Smarty object
     *
     * @var Smarty
     */
    public $smarty = null;

    /**
     * Parent object
     *
     * @var object
     */
    public $parent = null;

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
    public $_usage = Smarty::IS_TEMPLATE;

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
     * caching mode
     *
     * @var int
     */
    public $caching = null;

    /**
     * compile id
     *
     * @var mixed
     */
    public $compile_id = null;

    /**
     * cache id
     *
     * @var mixed
     */
    public $cache_id = null;

    /**
     * cache life time
     *
     * @var int
     */
    public $cache_lifetime = 0;

    /**
     * no_output_filter
     *
     * @var bool
     */
    public $no_output_filter = false;

    /**
     * scope_type
     *
     *
     * @var int
     */
    public $scope_type = 0;

    /**
     * variable pairs
     *
     *
     * @var array
     */
    public $data = null;

    /**
     *
     * force cache
     *
     * @var bool
     */
    public $force_caching = false;

    /**
     *
     * storage for source content used by some resource
     *
     * @var string
     */
    public $content = null;

    /**
     * key counter
     *
     * @var int
     */
    public static $_key_counter = 0;

    /**
     * key number for this context object
     *
     * @var int
     */
    public $_key = null;

    /**
     * Create source object and populate is it source info
     *
     * @param Smarty $smarty smarty object
     * @param string $name name part of template specification
     * @param string $type type of source resource handler
     * @param object $parent
     * @param bool $isConfig
     */
    public function __construct(Smarty $smarty, $name, $type, $parent = null, $isConfig = false)
    {
        $this->force_caching = $smarty->object_caching;
        $this->smarty = $smarty;
        if ($isConfig) {
            $this->_usage = Smarty::IS_CONFIG;
        }
        $this->name = $name;
        $this->type = $type;
        // get Resource handler
        if (isset(Smarty::$_resource_cache[Smarty::SOURCE][$type])) {
            $this->handler = Smarty::$_resource_cache[Smarty::SOURCE][$type];
        } else {
            $this->handler = $smarty->_loadResource(Smarty::SOURCE, $type);
        }
        // parent needed in populate for relative template path
        $this->parent = $parent;
        $this->handler->populate($this);
        $this->_key = self::$_key_counter++;
        return $this;
    }

    /**
     * wrapper to read source
     *
     * @return boolean false|string
     */
    public function getContent()
    {
        return $this->handler->getContent($this);
    }
}
