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
     * compiled cache
     *
     * @var array
     */
    public $compiled = array();

    /**
     * cached cache
     *
     * @var array
     */
    public $cached = array();


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
        if (isset(Smarty::$_resource_cache[Smarty::SOURCE][$type])) {
            $this->handler = Smarty::$_resource_cache[Smarty::SOURCE][$type];
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

    /**
     * @param  Smarty $smarty
     * @param  int $resource_group  SOURCE|COMPILED|CACHE
     * @param  null|Smarty|Smarty_Data|Smarty_Template  $parent     parent scope
     * @param  mixed $compile_id       compile id to be used with this template
     * @param  mixed $cache_id         cache id to be used with this template
     * @param  int $caching
     * @param  int $cache_lifetime
     * @param  null | array $data       array of variable names/values
     * @param  int $scope_type
     * @param  bool $no_output_filter if true do not run output filter
     * @param  bool $display          true: display, false: fetch
     * @return string   rendered template HTML output
     */
    public function _getRenderedTemplate($smarty, $resource_group, $parent, $compile_id, $cache_id, $caching, $cache_lifetime, $data, $scope_type, $no_output_filter = false, $display = false)
    {
            // build variable scope
            $scope = $this->_buildScope ($smarty, $parent, $scope_type, $data);

            // get template object
            $template_obj = $this->_getTemplateObject($smarty, $resource_group, $parent, $compile_id, $cache_id, $caching, $cache_lifetime, $data, $scope_type, $no_output_filter, $display, $scope);
            if ($template_obj === false) {

            }
            //render template
            return $template_obj->getRenderedTemplate($parent, $scope, $scope_type, $no_output_filter, $display);
    }

    /**
     * @param  Smarty $smarty
     * @param  int $resource_group  SOURCE|COMPILED|CACHE
     * @param  null|Smarty|Smarty_Data|Smarty_Template  $parent     parent scope
     * @param  mixed $compile_id       compile id to be used with this template
     * @param  mixed $cache_id         cache id to be used with this template
     * @param  int $caching
     * @param  int $cache_lifetime
     * @param  null | array $data       array of variable names/values
     * @param  int $scope_type
     * @param  bool $no_output_filter if true do not run output filter
     * @param  bool $display          true: display, false: fetch
     * @param  Smarty_Variable_Scope $scope
     * @return Smarty_Template  template object
     */
    public function _getTemplateObject($smarty, $resource_group, $parent, $compile_id, $cache_id, $caching, $cache_lifetime = 0, $data =null, $scope_type = null, $no_output_filter = false, $display = false, $scope = null)
    {
        if ($resource_group != Smarty::SOURCE) {
            $compile_key = isset($compile_id) ? $compile_id : '';
            $caching_key = (($caching) ? 1 : 0);
            if ($resource_group == Smarty::COMPILED) {
                if ($this->recompiled) {
                    $type_key = $compiled_type = 'recompiled';
                } else {
                    $type_key = $compiled_type = $smarty->compiled_type;
                }
                if (isset($this->compiled[$compile_key][$caching_key][$type_key])) {
                    $template_obj = $this->compiled[$compile_key][$caching_key][$type_key];
                } else {
                    // get compiled resource object
                    $res_obj = isset(Smarty::$_resource_cache[Smarty::COMPILED][$compiled_type]) ? Smarty::$_resource_cache[Smarty::COMPILED][$compiled_type] : $smarty->_loadResource(Smarty::COMPILED, $compiled_type);
                    $template_obj = $this->compiled[$compile_key][$caching_key][$type_key] = $res_obj->instanceTemplate($smarty, $this, $compile_id, $caching);
                }
            }
            if ($resource_group == Smarty::CACHE) {
                $cache_key = isset($cache_id) ? $cache_id : '';
                if (isset($this->cached[$compile_key][$cache_key][$smarty->caching_type])) {
                    $template_obj = $this->cached[$compile_key][$cache_key][$smarty->caching_type];
                } else {
                    // get cached resource object
                    $res_obj = isset(Smarty::$_resource_cache[Smarty::CACHE][$smarty->caching_type]) ? Smarty::$_resource_cache[Smarty::CACHE][$smarty->caching_type] : $smarty->_loadResource(Smarty::CACHE, $smarty->caching_type);
                   // build variable scope
                    if ($scope == null) {
                        $scope = $this->_buildScope($smarty, $parent, $scope_type, $data);
                    }
                    $template_obj = $this->cached[$compile_key][$cache_key][$smarty->caching_type] = $res_obj->instanceTemplate($smarty, $this, $compile_id, $cache_id,
                        $caching, $cache_lifetime, $parent, $scope, $scope_type, $no_output_filter);
                }
            }

            return $template_obj;
        }
    }

    /**
     * Build variable scope
     *
     * @internal
     * @param   Smarty                              $smarty
     * @param   null|Smarty|Smarty_Data|Smarty_Template  $parent     parent socpe
     * @param   int                                 $scope_type
     * @param   null | array                        $data       array of variable names/values
     * @return  Smarty_Variable_Scope    merged tpl vars
     */
    public function _buildScope ($smarty, $parent, $scope_type, $data = null)
    {
        // local variable scope for this call
        if ($parent instanceof Smarty_Template) {
            $scope = clone $parent->_tpl_vars;
        } else {
            if ($parent == null || $parent == $smarty) {
                $scope = clone $smarty->_tpl_vars;
            } else {
                $scope = $this->_mergeScopes($parent);
                foreach($smarty->_tpl_vars as $var => $obj) {
                    $scope->$var = $obj;
                }
            }
            // merge global variables
            foreach (Smarty::$_global_tpl_vars as $var => $obj) {
                if (!isset($scope->$var)) {
                    $scope->$var = $obj;
                }
            }
        }

        // fill data if present
        if ($data != null) {
            // set up variable values
            foreach ($data as $var => $value) {
                if ($value instanceof Smarty_Variable) {
                    $scope->$var = $value;
                } else {
                    $scope->$var = new Smarty_Variable($value);
                }
            }
        }
        return $scope;
    }

    /**
     *
     *  merge recursively template variables into one scope
     *
     * @internal
     * @param   Smarty|Smarty_Data|Smarty_Template $ptr
     * @return Smarty_Variable_Scope    merged tpl vars
     */
    public function _mergeScopes($ptr)
    {
        // Smarty::triggerTraceCallback('trace', ' merge tpl ');

        if (isset($ptr->parent)) {
            $scope = $this->_mergeScopes($ptr->parent);
            foreach ($ptr->_tpl_vars as $var => $obj) {
                $scope->$var = $obj;
            }

            return $scope;
        } else {
            return clone $ptr->_tpl_vars;
        }
    }

}
