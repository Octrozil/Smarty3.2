<?php

/**
 * Smarty Internal Plugin Smarty Internal Content
 *
 * This file contains the basic shared methods for precessing content of compiled and cached templates
 *
 *
 * @package Template
 * @author Uwe Tews
 */

/**
 * Class with shared content processing methods
 *
 *
 * @package Template
 */
class Smarty_Template_Class extends Smarty_Exception_Magic
{

    /**
     * flag if class is valid
     * @var boolean assumed true
     * @internal
     */
    public static $isValid = false;

    /**
     * Smarty object
     * @var Smarty
     */
    public $tpl_obj = null;

    /**
     * Parent object
     * @var Smarty|Smarty_Data|Smarty_Template_Class
     */
    public $parent = null;

    /**
     * Source object
     * @var Smarty_Source_Resource
     */
    public $source = null;

    /**
     * Local variable scope
     * @var Smarty_Variable_Scope
     */
    public $tpl_vars = null;

    /**
     * flag if class is from cache file
     * @var boolean
     * @internal
     */
    public $is_cache = false;

    /**
     * flag if content does contain nocache code
     * @var boolean
     * @internal
     */
    public $has_nocache_code = false;

    /**
     * saved cache lifetime
     * @var int
     * @internal
     */
    public $cache_lifetime = false;

    /**
     * names of cached subtemplates
     * @var array
     * @internal
     */
    public $cached_subtemplates = array();

    /**
     * required plugins
     * @var array
     * @internal
     */
    public $required_plugins = array();

    /**
     * required plugins of nocache code
     * @var array
     * @internal
     */
    public $required_plugins_nocache = array();

    /**
     * template function properties
     *
     * @var array
     */
    public $tpl_obj_functions = array();

    /**
     * template functions called nocache
     * @var array
     */
    public $called_nocache_template_functions = array();

    /**
     * file dependencies
     *
     * @var array
     */
    public $file_dependency = array();

    /**
     * Smarty version class was compiled with
     * @var string
     * @internal
     */
    public $version = '';

    /**
     * flag if content is inheritance child
     *
     * @var bool
     */
    public $is_inheritance_child = false;

    /**
     * Timestamp
     * @var integer
     */
    public $timestamp = null;

    /**
     * Template Compile Id (Smarty::$compile_id)
     * @var string
     */
    public $compile_id = null;

    /**
     * Flag if caching enabled
     * @var boolean
     */
    public $caching = false;

    /**
     * internal capture runtime stack
     * @var array
     */
    public $_capture_stack = array(0 => array());

    /**
     * call stack
     * @var array
     */
    public static $call_stack = array();

    /**
     * constructor
     *
     * @param Smarty $smarty Smarty object
     * @param Smarty|Smarty_Data|Smarty_Template_Class $parent parent object
     * @params Smarty_Source_Resource $source source resource
     */
    public function __construct($tpl_obj, $parent, $source)
    {
        $this->tpl_obj = $tpl_obj;
        $this->parent = $parent;
        $this->source = $source;
        if (!self::$isValid) {
            // check if class is still valid
            if ($this->version != Smarty::SMARTY_VERSION) {
                // not valid because new Smarty version
                return;
            }
            if ((!$this->is_cache && $this->tpl_obj->compile_check) || ($this->is_cache && ($this->tpl_obj->compile_check === true || $this->tpl_obj->compile_check === Smarty::COMPILECHECK_ON)) && !empty($this->file_dependency)) {
                foreach ($this->file_dependency as $_file_to_check) {
                    if ($_file_to_check[2] == 'file' || $_file_to_check[2] == 'php') {
                        if ($this->source->filepath == $_file_to_check[0]) {
                            // do not recheck current template
                            continue;
                        } else {
                            // file and php types can be checked without loading the respective resource handlers
                            $mtime = @filemtime($_file_to_check[0]);
                        }
                    } elseif ($_file_to_check[2] == 'string') {
                        continue;
                    } else {
                        $source = Smarty_Resource_Source::load($this->tpl_obj, $_file_to_check[0]);
                        $mtime = $source->timestamp;
                    }
                    if (!$mtime || $mtime > $_file_to_check[1]) {
                        // not valid because newer dependent resource/file
                        return;
                    }
                }
            }
            foreach ($this->required_plugins as $file => $call) {
                if (!is_callable($call)) {
                    include $file;
                }
            }
            self::$isValid = true;
        }
        if (!$this->is_cache) {
            if (!empty($this->template_functions) && isset($tpl_obj->parent) && $tpl_obj->parent->usage == Smarty::IS_TEMPLATE) {
                $tpl_obj->parent->template_function_chain = $tpl_obj;
            }
        }
    }

    /**
     * get rendered template output from compiled template
     *
     * @param  int $scope_type
     * @param  null|array $data
     * @param  boolean $no_output_filter true if output filter shall nit run
     * @throws Exception
     * @return string
     */
    public function getRenderedTemplate($scope_type = Smarty::SCOPE_LOCAL, $data = null, $no_output_filter = true)
    {
        $this->_buildScope($scope_type, $data);
        $this->tpl_obj->cached_subtemplates = array();
        try {
            $level = ob_get_level();
            if ($this->tpl_obj->debugging) {
                Smarty_Debug::start_render($this->source);
            }
            array_unshift(self::$call_stack, $this);
            array_unshift($this->_capture_stack, array());
            //
            // render compiled template
            //
            $output = $this->_renderTemplate($this->tpl_obj, $this->tpl_vars);
            array_shift(self::$call_stack);
            // any unclosed {capture} tags ?
            if (isset($this->_capture_stack[0][0])) {
                $this->tpl_obj->_capture_error();
            }
            array_shift($this->_capture_stack);
        } catch (Exception $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
            throw $e;
        }
//        if ($this->source->recompiled && empty($this->file_dependency[$this->source->uid])) {
//            $this->file_dependency[$this->source->uid] = array($this->source->filepath, $this->source->timestamp, $this->source->type);
//        }
        if ($this->caching) {
            Smarty_Cache_Resource::$creator[0]->_mergeFromCompiled($this);
        }
        if (!$no_output_filter && (isset($this->tpl_obj->autoload_filters['output']) || isset($this->tpl_obj->registered_filters['output']))) {
            $output = $this->tpl_obj->runFilter('output', $output);
        }

        if ($this->tpl_obj->debugging) {
            Smarty_Debug::end_render($this->source);
        }

        return $output;
    }

    /**
     *
     *  runtime routine to create a new variable scope
     *
     * @param  int $scope_type
     * @param  null $data
     * @return array|null|\Smarty_Variable_Scope|\stdClass
     */
    public function _buildScope($scope_type = Smarty::SCOPE_LOCAL, $data = null)
    {
        if (true) {
            switch ($scope_type) {
                case Smarty::SCOPE_LOCAL:
                    if ($this->parent == null) {
                        $this->tpl_vars = new Smarty_Variable_Scope();
                        break;
                    }
                    if ($this->parent instanceof Smarty_Variable_Scope) {
                        $this->tpl_vars = clone $this->parent;
                        break;
                    }
                    if ($this->parent->usage == Smarty::IS_SMARTY || $this->parent->usage == Smarty::IS_TEMPLATE) {
                        $this->tpl_vars = clone $this->parent->tpl_vars;
                        break;
                    }
                    $this->tpl_vars = $this->_mergeScopes($this->parent);
                    break;
                case Smarty::SCOPE_PARENT:
                    $this->tpl_vars = $this->parent->tpl_vars;
                    break;
                case Smarty::SCOPE_GLOBAL:
                    $this->tpl_vars = Smarty::$global_tpl_vars;
                    break;
                case Smarty::SCOPE_ROOT:
                    $ptr = $this;
                    while ($ptr->parent && $ptr->parent->usage == Smarty::IS_TEMPLATE) {
                        $ptr = $ptr->parent;
                    }
                    $this->tpl_vars = $ptr->tpl_vars;
                    break;
            }
        } else {
            $this->tpl_vars = $this->parent->tpl_vars;
        }

        // create special smarty variable
        if (!isset($this->tpl_vars->smarty)) {
            $this->tpl_vars->smarty = new Smarty_Variable();
        }
        // fill data if present
        if ($data != null) {
            // set up variable values
            foreach ($data as $varname => $value) {
                $this->tpl_vars->$varname = new Smarty_Variable($value);
            }
        }

        return;
    }

    /**
     *
     *  merge tpl vars
     *
     * @param  Smarty|Smarty_Data|Smarty_Template_Class $ptr
     * @return Smarty_Variable_Scope                    merged tpl vars
     */
    public function _mergeScopes($ptr)
    {
        // Smarty::triggerCallback('trace', ' merge tpl ');

        if ($ptr->parent) {
            $tpl_vars = $this->_mergeScopes($ptr->parent);
            foreach ($ptr->tpl_vars as $var => $data) {
                $tpl_vars->$var = $data;
            }

            return $tpl_vars;
        } else {
            return clone $ptr->parent->tpl_vars;
        }
    }

    /**
     * Template runtime function to call a template function
     *
     * @param  string $name    name of template function
     * @param  Smarty $tpl_obj calling template object
     * @param  Smarty_Variable_Scope $_scope
     * @param  array $params  array with calling parameter
     * @param  string $assign  optional template variable for result
     * @throws Smarty_Exception_Runtime
     * @return bool
     */
    public function _callTemplateFunction($name, $tpl_obj, $_scope, $params, $assign)
    {
        if ($this->is_cache && isset($tpl_obj->cached->template_obj->template_functions[$name])) {
            $content_ptr = $tpl_obj->cached->template_obj;
        } else {
            $ptr = $tpl = $tpl_obj;
            while ($ptr != null && !isset($ptr->compiled->template_obj->template_functions[$name])) {
                $ptr = $ptr->template_function_chain;
                if ($ptr == null && $tpl->parent->usage == Smarty::IS_TEMPLATE) {
                    $ptr = $tpl = $tpl->parent;
                }
            }
            if (isset($ptr->compiled->template_obj->template_functions[$name])) {
                $content_ptr = $ptr->compiled->template_obj;
            }
        }
        if (isset($content_ptr)) {
            if (!empty($assign)) {
                ob_start();
            }
            $func_name = "_renderTemplateFunction_{$name}";
            $content_ptr->$func_name($tpl_obj, $_scope, $params);
            if (!empty($assign)) {
                $tpl_obj->assign($assign, ob_get_clean());
            }

            return true;
        }
        throw new Smarty_Exception_Runtime("Call to undefined template function '{$name}'", $tpl_obj);
    }

    /**
     * [util function] counts an array, arrayaccess/traversable or PDOStatement object
     *
     * @param  mixed $value
     * @return int   the count for arrays and objects that implement countable, 1 for other objects that don't, and 0 for empty elements
     */
    public function _count($value)
    {
        if (is_array($value) === true || $value instanceof Countable) {
            return count($value);
        } elseif ($value instanceof IteratorAggregate) {
            // Note: getIterator() returns a Traversable, not an Iterator
            // thus rewind() and valid() methods may not be present
            return iterator_count($value->getIterator());
        } elseif ($value instanceof Iterator) {
            return iterator_count($value);
        } elseif ($value instanceof PDOStatement) {
            return $value->rowCount();
        } elseif ($value instanceof Traversable) {
            return iterator_count($value);
        } elseif ($value instanceof ArrayAccess) {
            if ($value->offsetExists(0)) {
                return 1;
            }
        } elseif (is_object($value)) {
            return count($value);
        }

        return 0;
    }

    /**
     * Template code runtime function to create a local Smarty variable for array assignments
     *
     * @param string $tpl_var template variable name
     * @param Smarty_Variable_Scope $_scope  variable scope
     * @param bool $nocache cache mode of variable
     */
    public function _createLocalArrayVariable($tpl_var, $_scope, $nocache = false)
    {
        if (isset($_scope->{$tpl_var})) {
            $_scope->{$tpl_var} = clone $_scope->{$tpl_var};
// TODO
//        } elseif ($result = $_scope->___attributes->tpl_ptr->getVariable($tpl_var, $_scope->___attributes->tpl_ptr->parent, true, false)) {
//            $_scope->{$tpl_var} = clone $result;
        } else {
            $_scope->{$tpl_var} = new Smarty_Variable(array(), $nocache);

            return;
        }
        $_scope->{$tpl_var}->nocache = $nocache;
        if (!(is_array($_scope->{$tpl_var}->value) || $_scope->{$tpl_var}->value instanceof ArrayAccess)) {
            settype($_scope->{$tpl_var}->value, 'array');
        }
    }


    /**
     * Template code runtime function to get subtemplate content
     *
     * @param  string $template_resource the resource handle of the template file
     * @param  mixed $cache_id         cache id to be used with this template
     * @param  mixed $compile_id       compile id to be used with this template
     * @param  integer $caching          cache mode
     * @param  integer $cache_lifetime   life time of cache data
     * @param  array $data             array with parameter template variables
     * @param  int $scope_type       scope in which {include} should execute
     * @param  Smarty_Variable_Scope $_scope
     * @param  string $content_class    optional name of inline content class
     * @return string                template content
     */
    public function _getSubTemplate($template_resource, $cache_id, $compile_id, $caching, $cache_lifetime, $data, $scope_type, $_scope, $content_class = null)
    {
        if (isset($content_class)) {
            // clone new template object
            $tpl_obj = clone $parent_tpl_obj;
            $tpl_obj->template_resource = $template_resource;
            $tpl_obj->cache_id = $cache_id;
            $tpl_obj->compile_id = $compile_id;
            $tpl_obj->parent = $parent_tpl_obj;

            // instance content class
            $tpl_obj->compiled = new stdclass;
            $tpl_obj->compiled->template_obj = new $content_class($tpl);
            $result = $tpl_obj->compiled->getRenderedTemplate($tpl_obj, $_scope, $scope_type, $data, $no_output_filter);
//            $result = $tpl->compiled->template_obj->_renderTemplate($tpl);
            unset($tpl_obj->tpl_vars, $tpl_obj);

            return $result;
        } else {
            if ($this->tpl_obj->caching && $caching && $caching != Smarty::CACHING_NOCACHE_CODE) {
                $this->tpl_obj->cached_subtemplates[$template_resource] = array($template_resource, $cache_id, $compile_id, $caching, $cache_lifetime);
            }

            return $this->tpl_obj->fetch($template_resource, $cache_id, $compile_id, $_scope, false, true, $data, $scope_type, $caching, $cache_lifetime);
        }

    }

    /**
     * [util function] to use either var_export or unserialize/serialize to generate code for the
     * cachevalue optionflag of {assign} tag
     *
     * @param  mixed $var Smarty variable value
     * @throws Smarty_Exception
     * @return string           PHP inline code
     */
    public function _exportCacheValue($var)
    {
        if (is_int($var) || is_float($var) || is_bool($var) || is_string($var) || (is_array($var) && !is_object($var) && !array_reduce($var, array($this, '_checkAarrayCallback')))) {
            return var_export($var, true);
        }
        if (is_resource($var)) {
            throw new Smarty_Exception('Cannot serialize resource');
        }

        return 'unserialize(\'' . serialize($var) . '\')';
    }

    /**
     * callback used by _export_cache_value to check arrays recursively
     *
     * @param  bool $flag    status of previous elements
     * @param  mixed $element array element to check
     * @throws Smarty_Exception
     * @return bool             status
     */
    private function _checkAarrayCallback($flag, $element)
    {
        if (is_resource($element)) {
            throw new Smarty_Exception('Cannot serialize resource');
        }
        $flag = $flag || is_object($element) || (!is_int($element) && !is_float($element) && !is_bool($element) && !is_string($element) && (is_array($element) && array_reduce($element, array($this, '_checkAarrayCallback'))));

        return $flag;
    }

    /**
     * Template code runtime function to load config varibales
     *
     * @param object $tpl_obj calling template object
     */
    public function _loadConfigVars($tpl_obj)
    {
        $ptr = $tpl_obj->parent;
        $this->_loadConfigValuesInScope($tpl_obj, $ptr->tpl_vars);
        $ptr = $ptr->parent;
        if ($tpl_obj->tpl_vars->___config_scope == 'parent' && $ptr != null) {
            $this->_loadConfigValuesInScope($tpl_obj, $ptr->tpl_vars);
        }
        if ($tpl_obj->tpl_vars->___config_scope == 'root' || $tpl_obj->tpl_vars->___config_scope == 'global') {
            while ($ptr != null && $ptr->usage == Smarty::IS_TEMPLATE) {
                $this->_loadConfigValuesInScope($tpl_obj, $ptr->tpl_vars);
                $ptr = $ptr->parent;
            }
        }
        if ($tpl_obj->tpl_vars->___config_scope == 'root') {
            while ($ptr != null) {
                $this->_loadConfigValuesInScope($tpl_obj, $ptr->tpl_vars);
                $ptr = $ptr->parent;
            }
        }
        if ($tpl_obj->tpl_vars->___config_scope == 'global') {
            $this->_loadConfigValuesInScope($tpl_obj, Smarty::$global_tpl_vars);
        }
    }

    /**
     * Template code runtime function to load config varibales into a single scope
     *
     * @param object $tpl_obj  calling template object
     * @param object $tpl_vars variable container of scope
     */
    public function _loadConfigValuesInScope($tpl_obj, $tpl_vars)
    {
        foreach ($this->config_data['vars'] as $var => $value) {
            if ($tpl_obj->config_overwrite || !isset($tpl_vars->$var)) {
                $tpl_vars->$var = $value;
            } else {
                $tpl_vars->$var = array_merge((array)$tpl_vars->{$var}, (array)$value);
            }
        }
        if (isset($this->config_data['sections'][$tpl_obj->tpl_vars->___config_sections])) {
            foreach ($this->config_data['sections'][$tpl_obj->tpl_vars->___config_sections]['vars'] as $var => $value) {
                if ($tpl_obj->config_overwrite || !isset($tpl_vars->$var)) {
                    $tpl_vars->$var = $value;
                } else {
                    $tpl_vars->$var = array_merge((array)$tpl_vars->{$var}, (array)$value);
                }
            }
        }
    }

}
