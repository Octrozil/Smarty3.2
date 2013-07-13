<?php

/**
 * Smarty Internal Plugin
 *
 *
 * @package Cacher
 */

/**
 * Cache Support Routines To Create Cache
 *
 *
 * @package Cacher
 * @author Uwe Tews
 */
class Smarty_Cache_Helper_Create extends Smarty_Exception_Magic
{

    /**
     * Code Object
     * @var Smarty_Compiler_Code
     */
    public $template_code = null;

    /**
     * required plugins
     * @var array
     * @internal
     */
    public $required_plugins = array();

    /**
     * template function properties
     *
     * @var array
     */
    public $template_functions = array();

    /**
     * template function properties
     *
     * @var array
     */
    public $template_functions_code = array();

    /**
     * block function properties
     *
     * @var array
     */
    public $inheritance_blocks = array();

    /**
     * block function compiled code
     *
     * @var array
     */
    public $inheritance_blocks_code = array();

    /**
     * file dependencies
     *
     * @var array
     */
    public $file_dependency = array();

    /**
     * flag if cache does have nocache code
     *
     * @var boolean
     */
    public $has_nocache_code = false;

    /*
     * Internal class to render new cached content
     *
     * @var Smarty_Template_Class
     */
    public $template_obj = null;

    // dummmy
    public $isValid;

    /**
     * Find template object of cache file and return Smarty_template_Cached
     *
     * @param  Smarty                 $tpl_obj current template
     * @return Smarty_template_Cached
     */
    public static function _getCachedObject($tpl_obj)
    {
        $_tpl = $tpl_obj;
        while ($_tpl->usage == Smarty::IS_TEMPLATE) {
            if (isset($_tpl->cached)) {
                break;
            }
            $_tpl = $_tpl->parent;
        }

        return $_tpl->cached;
    }

    /**
     * Create new cache file
     *
     * @param $cache_obj            cache object
     * @param  Smarty                $tpl_obj          current template
     * @param  string                $output           cache file content
     * @param  Smarty_Variable_Scope $_scope
     * @param  boolean               $no_output_filter flag that output shall not run through filter
     * @throws Exception
     * @return string
     */
    public function _createCacheFile($cache_obj, $tpl_obj, $output, $_scope, $no_output_filter)
    {
        if ($tpl_obj->debugging) {
            Smarty_Debug::start_cache($cache_obj->source);
        }
        $this->template_code = new Smarty_Compiler_Code(3);
        // get text between non-cached items
        $cache_split = preg_split("!/\*%%SmartyNocache%%\*/(.+?)/\*/%%SmartyNocache%%\*/!s", $output);
        // get non-cached items
        preg_match_all("!/\*%%SmartyNocache%%\*/(.+?)/\*/%%SmartyNocache%%\*/!s", $output, $cache_parts);
        unset($output);
        // loop over items, stitch back together
        foreach ($cache_split as $curr_idx => $curr_split) {
            if (!empty($curr_split)) {
                $this->template_code->php("echo ")->string($curr_split)->raw(";\n");
            }
            if (isset($cache_parts[0][$curr_idx])) {
                $this->has_nocache_code = true;
                // format and add nocache PHP code
                $this->template_code->formatPHP($cache_parts[1][$curr_idx]);
            }
        }
        if (!$no_output_filter && !$this->has_nocache_code && (isset($tpl_obj->autoload_filters['output']) || isset($tpl_obj->registered_filters['output']))) {
            $this->template_code->buffer = Smarty_Misc_FilterHandler::runFilter('output', $this->template_code->buffer, $tpl_obj);
        }
        // write cache file content
        if (!$cache_obj->source->recompiled && ($cache_obj->caching == Smarty::CACHING_LIFETIME_CURRENT || $cache_obj->caching == Smarty::CACHING_LIFETIME_SAVED)) {
            $this->template_code = $this->_createSmartyContentClass($tpl_obj);
            $cache_obj->writeCache($tpl_obj, $this->template_code->buffer);
            $cache_obj->populate($tpl_obj);
            $this->template_code = null;
            try {
                $level = ob_get_level();
                $output = $cache_obj->template_obj->_renderTemplate($tpl_obj, $_scope);
            } catch (Exception $e) {
                while (ob_get_level() > $level) {
                    ob_end_clean();
                }
                throw $e;
            }
        }
        if ($tpl_obj->debugging) {
            Smarty_Debug::end_cache($cache_obj->source);
        }

        return $output;
    }

    /**
     * Create Smarty content class for cache files
     *
     * @param  Smarty $tpl_obj template object
     * @return string
     */
    public function _createSmartyContentClass(Smarty $tpl_obj)
    {
        $template_code = new Smarty_Compiler_Code();
        $template_code->php("<?php /* Smarty version " . Smarty::SMARTY_VERSION . ", created on " . strftime("%Y-%m-%d %H:%M:%S") . " */")->newline();
        // content class name
        $class = '_SmartyTemplate_' . str_replace('.', '_', uniqid('', true));
        $template_code->php("if (!class_exists('{$class}',false)) {")->newline()->indent()->php("class {$class} extends Smarty_Template_Class" . (!empty($this->inheritance_blocks_code) ? "_Inheritance" : '') . " {")->newline()->indent();
        $template_code->php("public \$version = '" . Smarty::SMARTY_VERSION . "';")->newline();
        $template_code->php("public \$has_nocache_code = " . ($this->has_nocache_code ? 'true' : 'false') . ";")->newline();
        if (!empty($tpl_obj->cached_subtemplates)) {
            $template_code->php("public \$cached_subtemplates = ")->repr($tpl_obj->cached_subtemplates, false)->raw(";")->newline();
        }
        $template_code->php("public \$is_cache = true;")->newline();
        $template_code->php("public \$cache_lifetime = {$tpl_obj->cache_lifetime};")->newline();
        $template_code->php("public \$file_dependency = ")->repr($this->file_dependency, false)->raw(";")->newline();
        if (!empty($this->required_plugins)) {
            $template_code->php("public \$required_plugins = ")->repr($this->required_plugins, false)->raw(";")->newline();
        }
        if (!empty($this->template_functions)) {
            $template_code->php("public \$template_functions = ")->repr($this->template_functions, false)->raw(";")->newline();
        }
        $this->template_functions = array();
        if (!empty($this->inheritance_blocks)) {
            $template_code->php("public \$inheritance_blocks = ")->repr($this->inheritance_blocks, false)->raw(';')->newline();
        }
        $template_code->newline()->php("function _renderTemplate (\$_smarty_tpl, \$_scope) {")->newline()->indent();
        $template_code->php("ob_start();")->newline();
        $template_code->mergeCode($this->template_code);
        $template_code->php('return ob_get_clean();')->newline();
        $template_code->outdent()->php('}')->newline()->newline();
        foreach ($this->template_functions_code as $code) {
            $template_code->newline()->raw($code);
        }
        $this->template_functions_code = array();
        foreach ($this->inheritance_blocks_code as $code) {
            $template_code->newline()->raw($code);
        }

        $template_code->php("function _getSourceInfo () {")->newline()->indent();
        $template_code->php("return ")->repr($template_code->traceback)->raw(";")->newline();
        $template_code->outdent()->php('}')->newline();

        $template_code->outdent()->php('}')->newline()->outdent()->php('}')->newline();

        return $template_code;
    }

    /**
     * Merge plugin info, dependencies and nocache template functions into cache
     *
     * @param Smarty_Compiled_Resource $comp_obj compiled object
     */
    public function _mergeFromCompiled($comp_obj)
    {
        $this->required_plugins = array_merge($this->required_plugins, $comp_obj->template_obj->required_plugins_nocache);
        $this->file_dependency = array_merge($this->file_dependency, $comp_obj->template_obj->file_dependency);
        $this->has_nocache_code = $this->has_nocache_code || $comp_obj->template_obj->has_nocache_code;

        if (!empty($comp_obj->template_obj->called_nocache_template_functions)) {
            foreach ($comp_obj->template_obj->called_nocache_template_functions as $name => $dummy) {
                self::_mergeNocacheTemplateFunction($tpl_obj, $name);
            }
        }

    }

    /**
     * Merge plugin info, dependencies and nocache template functions into cache
     *
     * @param Smarty $template current template
     * @param string $name     name of template function
     */
    public function _mergeNocacheTemplateFunction($template, $name)
    {
        if (isset($this->template_functions[$name])) {
            return;
        }
        $ptr = $tpl = $template;
        while ($ptr != null && !isset($ptr->compiled->template_obj->template_functions[$name])) {
            $ptr = $ptr->template_function_chain;
            if ($ptr == null && ($tpl->parent->usage == Smarty::IS_TEMPLATE || $tpl->parent->usage == Smarty::IS_CONFIG)) {
                $ptr = $tpl = $tpl->parent;
            }
        }
        if (isset($ptr->compiled->template_obj->template_functions[$name])) {
            if (isset($ptr->compiled->template_obj->template_functions[$name]['used_plugins'])) {
                foreach ($ptr->compiled->template_obj->template_functions[$name]['used_plugins'] as $key => $function) {
                    $this->required_plugins[$key] = $function;
                }
            }
            $this->template_code = new Smarty_Compiler_Code(3);
            $this->template_functions[$name] = $ptr->compiled->template_obj->template_functions[$name];
            $obj = new ReflectionObject($ptr->compiled->template_obj);
            $refFunc = $obj->getMethod("_renderTemplateFunction_{$name}");
            $file = $refFunc->getFileName();
            $start = $refFunc->getStartLine() - 1;
            $end = $refFunc->getEndLine();
            $source = file($file);
            for ($i = $start; $i < $end; $i++) {
                if (preg_match("!/\*%%SmartyNocache%%\*/!", $source[$i])) {
                    $this->template_code->formatPHP(stripcslashes(preg_replace("!echo\s(\"|')/\*%%SmartyNocache%%\*/|/\*/%%SmartyNocache%%\*/(\"|');!", '', $source[$i])));
                } else {
                    $this->template_code->buffer .= $source[$i];
                }
            }
            $this->template_functions_code[$name] = $this->template_code->buffer;
            $this->template_code = null;
            if (isset($ptr->compiled->template_obj->template_functions[$name]['called_functions'])) {
                foreach ($ptr->compiled->template_obj->template_functions[$name]['called_functions'] as $name => $dummy) {
                    $this->_mergeNocacheTemplateFunction($template, $name);
                }
            }
        }
    }

    /**
     * Creates an inheritance block in cache file
     *
     * @param  object $current_tpl calling template
     * @param  string $name        name of block
     * @param  object $scope_tpl   blocks must be processed in this variable scope
     * @return string
     */
    // TODO has to be finished
    public function _createNocacheBlockChild($current_tpl, $name, $scope_tpl)
    {
        while ($current_tpl !== null && $current_tpl->usage == Smarty::IS_TEMPLATE) {
            if (isset($current_tpl->compiled->template_obj->inheritance_blocks[$name]['valid'])) {
                if (isset($current_tpl->compiled->template_obj->inheritance_blocks[$name]['hide'])) {
                    break;
                }
                if (isset($current_tpl->compiled->template_obj->inheritance_blocks[$name]['inc_child'])) {
                    $parent_tpl = $current_tpl;
                }
                if (isset($current_tpl->compiled->template_obj->inheritance_blocks[$name]['overwrite'])) {
                    $parent_tpl = null;
                }
                // back link pointers to inheritance parent template
                $template_stack[] = $current_tpl;
            }
            if ($status == 0 && ($current_tpl->is_inheritance_child || $current_tpl->compiled->template_obj->is_inheritance_child)) {
                $status = 1;
            }
            $current_tpl = $current_tpl->parent;
            if ($current_tpl === null || $current_tpl->usage != Smarty::IS_TEMPLATE || ($status == 1 && !$current_tpl->is_inheritance_child && !$current_tpl->compiled->template_obj->is_inheritance_child)) {
                // quit at first child of current inheritance chain
                break;
            }
        }
    }

    /**
     * Creates an inheritance block in cache file
     *
     * @param  object $current_tpl calling template
     * @param  string $name        name of block
     * @param  object $scope_tpl   blocks must be processed in this variable scope
     * @return string
     */
    public function _createNocacheInheritanceBlock($current_tpl, $name, $scope_tpl)
    {
        $output = '';
        $status = 0;
        $child_tpl = null;
        $parent_tpl = null;
        $template_stack = array();
        while ($current_tpl !== null && $current_tpl->usage == Smarty::IS_TEMPLATE) {
            if (isset($current_tpl->compiled->template_obj->inheritance_blocks[$name]['valid'])) {
                if (isset($current_tpl->compiled->template_obj->inheritance_blocks[$name]['hide'])) {
                    break;
                }
                $child_tpl = $current_tpl;
                if (isset($current_tpl->compiled->template_obj->inheritance_blocks[$name]['inc_child'])) {
                    $parent_tpl = $current_tpl;
                }
                if (isset($current_tpl->compiled->template_obj->inheritance_blocks[$name]['overwrite'])) {
                    $parent_tpl = null;
                }
                // back link pointers to inheritance parent template
                $template_stack[] = $current_tpl;
            }
            if ($status == 0 && ($current_tpl->is_inheritance_child || $current_tpl->compiled->template_obj->is_inheritance_child)) {
                $status = 1;
            }
            $current_tpl = $current_tpl->parent;
            if ($current_tpl === null || $current_tpl->usage != Smarty::IS_TEMPLATE || ($status == 1 && !$current_tpl->is_inheritance_child && !$current_tpl->compiled->template_obj->is_inheritance_child)) {
                // quit at first child of current inheritance chain
                break;
            }
        }

        if ($parent_tpl != null) {
            $child_tpl = $parent_tpl;
        }
        if ($child_tpl !== null) {
            $template_obj = $child_tpl->compiled->template_obj;

            if (isset($template_obj->inheritance_blocks[$name]['subblock'])) {
                foreach ($template_obj->inheritance_blocks[$name]['subblock'] as $subblock) {
                    $function = $template_obj->inheritance_blocks[$subblock]['function'];
                    $this->inheritance_blocks_code[$function] = $this->_getInheritanceBlockMethodSource($template_obj, $function);
                    $this->inheritance_blocks[$subblock]['function'] = $function;
                }
            }

            $function = $template_obj->inheritance_blocks[$name]['function'];
            $this->inheritance_blocks_code[$function] = $this->_getInheritanceBlockMethodSource($template_obj, $function);
            $this->inheritance_blocks[$name]['function'] = $function;
            $output = "/*%%SmartyNocache%%*/echo \$this->_getInheritanceBlock(\$_smarty_tpl, '{$name}', \$_smarty_tpl, 2);/*/%%SmartyNocache%%*/";
            if (isset($child_tpl->compiled->template_obj->inheritance_blocks[$name]['prepend'])) {
                $output .= $child_tpl->compiled->template_obj->_getInheritanceParentBlock($name, $template_stack, $scope_tpl);
            } elseif (isset($child_tpl->compiled->template_obj->inheritance_blocks[$name]['append'])) {
                $output = $child_tpl->compiled->template_obj->_getInheritanceParentBlock($name, $template_stack, $scope_tpl) . $output;
            }
        }

        return $output;
    }

    /**
     * Get block method source
     *
     * @param  object $template_obj Smarty content object
     * @param  string $function     method name of block
     * @return string source code
     */
    public function _getInheritanceBlockMethodSource($template_obj, $function)
    {
        $template_code = new Smarty_Compiler_Code(3);
        $obj = new ReflectionObject($template_obj);
        $refFunc = $obj->getMethod($function);
        $file = $refFunc->getFileName();
        $start = $refFunc->getStartLine() - 1;
        $end = $refFunc->getEndLine();
        $source = file($file);
        for ($i = $start; $i < $end; $i++) {
            if (preg_match("!/\*%%SmartyNocache%%\*/!", $source[$i])) {
                $template_code->formatPHP(stripcslashes(preg_replace("!echo\s(\"|')/\*%%SmartyNocache%%\*/|/\*/%%SmartyNocache%%\*/(\"|');!", '', $source[$i])));
            } else {
                $template_code->buffer .= $source[$i];
            }
        }

        return $template_code->buffer;
    }

}
