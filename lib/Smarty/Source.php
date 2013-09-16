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
class Smarty_Source extends Smarty_Exception_Magic
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
     * Flag if source needs no compiler
     * will be loaded by resource handler
     *
     * @var bool
     */
    public $uncompiled = false;

    /**
     * Flag if source needs to be always recompiled
     * will be loaded by resource handler
     *
     * @var bool
     */
    public $recompiled = false;

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
     * root of compiled and cache objects
     *
     * @var object
     */
    public $cacheRoot = null;


    /**
     * Create source object and populate is it source info
     *
     * @param Smarty $smarty smarty object
     * @param string $name   name part of template specification
     * @param string $type   type of source resource handler
     * @param bool $isConfig
     * @param Smarty $parent
     */
    public function __construct($smarty, $name, $type, $isConfig = false, $parent = null)
    {
        if ($isConfig) {
            $this->_usage = Smarty::IS_CONFIG;
        }
        $this->name = $name;
        $this->type = $type;
        // get Resource handler
        if (isset(Smarty::$resource_cache[Smarty::SOURCE][$type])) {
            $this->handler = Smarty::$resource_cache[Smarty::SOURCE][$type];
        } else {
            $this->handler = $smarty->_loadResource(Smarty::SOURCE, $type);
        }
        $this->recompiled = $this->handler->recompiled;
        $this->uncompiled = $this->handler->uncompiled;
        if (isset($this->handler->_allow_relative_path) && isset($parent)) {
            $this->handler->populate($smarty, $this, $parent);
        } else {
            $this->handler->populate($smarty, $this);
        }
        $this->cacheRoot = new stdClass();
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

    /**
     * Wrapper to Determine basename for compiled filename
     *
     * @return string resource's basename
     */
    public function getBasename()
    {
        return $this->handler->getBasename($this);
    }

    public function _getRenderedTemplate($smarty, $resource_group, $parent, $compile_id, $cache_id, $caching, $data, $scope_type, $no_output_filter = false, $display = false)
    {
        // local variable scope for this call
        if ($parent instanceof Smarty_Variable_Scope) {
            $scope = clone $parent;
        } elseif ($smarty->_usage == Smarty::IS_SMARTY) {
            $scope = clone $smarty->_tpl_vars;
        } else {
            $scope = $this->_mergeScopes($smarty);
        }
        // fill data if present
        if ($data != null) {
            // set up variable values
            foreach ($data as $varname => $value) {
                if ($value instanceof Smarty_Variable) {
                    $scope->$varname = $value;
                } else {
                    $scope->$varname = new Smarty_Variable($value);
                }
            }
        }


        if ($resource_group != Smarty::SOURCE) {
            $comp = 'c_' . $compile_id . '_o' . ($caching) ? 1 : 0;
          $ptr = isset($this->cacheRoot->$comp) ? $this->cacheRoot->$comp : $this->cacheRoot->$comp = new stdClass();
            $type = $resource_group . '_';
            if ($resource_group == Smarty::COMPILED) {
                if ($this->recompiled) {
                    $type .= $compiled_type = 'recompiled';
                } else {
                    $type .= $compiled_type = $smarty->compiled_type;
                }
                $ptr = isset($ptr->$type) ? $ptr->$type : $ptr->$type = new stdClass();
                if (isset($ptr->template_obj)) {
                    $template_obj = $ptr->template_obj;
                } else {
                    if (isset(Smarty::$resource_cache[Smarty::COMPILED][$compiled_type])) {
                        // resource already in cache
                        $res_obj = Smarty::$resource_cache[Smarty::COMPILED][$compiled_type];
                    } else {
                        $res_obj = $smarty->_loadResource(Smarty::COMPILED, $compiled_type);
                    }
                    $ptr->template_obj = $template_obj = $res_obj->instanceTemplate($smarty, $this, $compile_id, $caching);
                }
            }
            if ($resource_group == Smarty::CACHE) {
                $type .= $smarty->caching_type;
                $ptr = isset($ptr->$type) ? $ptr->$type : $ptr->$type = new stdClass();
                $cache_id = 'c_' . $cache_id . '_o';
                $ptr = isset($ptr->cache_id) ? $ptr->cache_id : $ptr->cache_id = new stdClass();
                if (isset($ptr->template_obj)) {
                    $template_obj = $ptr->template_obj;
                } else {
                    if (isset(Smarty::$resource_cache[Smarty::CACHE][$smarty->caching_type])) {
                        // resource already in cache
                        $res_obj = Smarty::$resource_cache[Smarty::CACHE][$smarty->caching_type];
                    } else {
                        $res_obj = $smarty->_loadResource(Smarty::CACHE, $smarty->caching_type);
                    }
                    $ptr->template_obj = $template_obj = $res_obj->instanceTemplate($smarty, $this, $compile_id, $cache_id,
                        $caching, $parent, $scope, $scope_type, $no_output_filter);
                }

            }
            //render template
            return $template_obj->getRenderedTemplate($parent, $scope, $scope_type, $no_output_filter, $display);
        }
    }

    /**
     *
     *  merge recursively template variables into one scope
     *
     * @param   Smarty|Smarty_Data|Smarty_Template $ptr
     * @return Smarty_Variable_Scope                    merged tpl vars
     */
    public function _mergeScopes($ptr)
    {
        // Smarty::triggerTraceCallback('trace', ' merge tpl ');

        if ($ptr->parent) {
            $_tpl_vars = $this->_mergeScopes($ptr->parent);
            foreach ($ptr->_tpl_vars as $var => $data) {
                $_tpl_vars->$var = $data;
            }

            return $_tpl_vars;
        } else {
            return clone $ptr->_tpl_vars;
        }
    }

}
