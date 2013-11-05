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
class Smarty_Context extends Smarty_Exception_Magic
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
     * Scope
     *
     *
     * @var Smarty_Variable_Scope
     */
    public $scope = null;

    /**
     * Template class name
     *
     *
     * @var string
     */
    public $tpl_class_name = null;

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
     * object cache
     *
     * @var int
     */
    public static $_object_cache = array();

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
     *
     * @internal
     * @param  int $resource_group SOURCE|COMPILED|CACHE
     * @param bool $nocache
     * @return string   rendered template HTML output
     */
    public function _getRenderedTemplate($resource_group, $nocache = false)
    {
        // build variable scope
        $this->scope = $this->_buildScope($this->smarty, $this->parent, $this->data);

        // get template object
        $template_obj = $this->_getTemplateObject($resource_group, $nocache);
        if ($template_obj === false) {
            return '';
        }
        //render template
        return $template_obj->getRenderedTemplate($this);
    }

    /**
     *
     * @internal
     * @param  int $resource_group SOURCE|COMPILED|CACHE
     * @param bool $nocache flag that template object shall not be cached
     * @return Smarty_Template  template object
     */
    public function _getTemplateObject($resource_group, $nocache = false)
    {
        $nocache = $nocache || $this->_usage == Smarty::IS_CONFIG;
        $do_cache = ($this->smarty->object_caching || $this->force_caching) && !$nocache;
        if ($this->handler->recompiled && $resource_group == Smarty::CACHE) {
            // we can't render from cache
            $resource_group = Smarty::COMPILED;
        }
        if ($resource_group != Smarty::SOURCE) {
            if ($do_cache) {
                $compile_key = isset($this->compile_id) ? $this->compile_id : '';
                $caching_key = (($this->caching) ? 1 : 0);
            }
            if ($resource_group == Smarty::COMPILED) {
                if ($this->handler->recompiled) {
                    $compiled_type = 'recompiled';
                } else {
                    $compiled_type = $this->smarty->compiled_type;
                }
                if ($this->smarty->object_caching && !$nocache && isset(self::$_object_cache[Smarty::COMPILED][$this->_key][$compile_key][$caching_key])) {
                    return self::$_object_cache[Smarty::COMPILED][$this->_key][$compile_key][$caching_key];
                }
                if (isset($this->tpl_class_name)) {
                    $template_obj = new $this->tpl_class_name($this);
                } else {
                    // get compiled resource object
                    $res_obj = isset(Smarty::$_resource_cache[Smarty::COMPILED][$compiled_type]) ? Smarty::$_resource_cache[Smarty::COMPILED][$compiled_type] : $this->smarty->_loadResource(Smarty::COMPILED, $compiled_type);
                    $template_obj = $res_obj->instanceTemplate($this);
                }
                if ($this->smarty->object_caching && !$nocache) {
                    self::$_object_cache[Smarty::COMPILED][$this->_key][$compile_key][$caching_key] = $template_obj;
                }
                return $template_obj;
            }
            if ($resource_group == Smarty::CACHE) {
                $caching_type = $this->smarty->caching_type;
                if ($do_cache) {
                    $cache_key = isset($this->cache_id) ? $this->cache_id : '';
                    if (isset(self::$_object_cache[Smarty::CACHE][$this->_key][$caching_type][$compile_key][$cache_key])) {
                        return self::$_object_cache[Smarty::CACHE][$this->_key][$caching_type][$compile_key][$cache_key];
                    }
                }
                // get cached resource object
                $res_obj = isset(Smarty::$_resource_cache[Smarty::CACHE][$caching_type]) ? Smarty::$_resource_cache[Smarty::CACHE][$caching_type] : $this->smarty->_loadResource(Smarty::CACHE, $caching_type);
                // build variable scope
                if ($this->scope == null) {
                    $this->scope = $this->_buildScope($this->smarty, $this->parent, $this->data);
                }
                $template_obj = $res_obj->instanceTemplate($this);
                if ($do_cache) {
                    self::$_object_cache[Smarty::CACHE][$this->_key][$caching_type][$compile_key][$cache_key] = $template_obj;
                }
                return $template_obj;
            }
        }
    }

    /**
     * Build variable scope
     *
     * @internal
     * @param   Smarty $smarty
     * @param   null|Smarty|Smarty_Data|Smarty_Template $parent parent socpe
     * @param   null | array $data array of variable names/values
     * @return  Smarty_Variable_Scope    merged tpl vars
     */
    public function _buildScope($smarty, $parent, $data = null)
    {
        if ($this->scope_type == Smarty::SCOPE_NONE) {
            $scope = new Smarty_Variable_Scope();
        } else {
            if ($parent instanceof Smarty_Template) {
                $scope = clone $parent->_tpl_vars;
            } else {
                if ($parent == null || $parent == $smarty) {
                    $scope = clone $smarty->_tpl_vars;
                } else {
                    $scope = $this->_mergeScopes($parent);
                    foreach ($smarty->_tpl_vars as $var => $obj) {
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
