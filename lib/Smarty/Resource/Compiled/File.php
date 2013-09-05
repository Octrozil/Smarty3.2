<?php

/**
 * Smarty Resource Compiled File Plugin
 *
 *
 * @package Resource\Compiled
 * @author Uwe Tews
 */

/**
 * Smarty Resource Compiled File Plugin
 * Meta Data Container for Compiled Template Files
 *
 */
class Smarty_Resource_Compiled_File extends Smarty_Exception_Magic
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
     * Template was recompiled
     * @var boolean
     */
    public $isCompiled = false;

    /**
     * file dependencies
     *
     * @var array
     */
    public $file_dependency = array();

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
     * Source Object
     * @var Smarty_Template_Source
     */
    public $source = null;

    /**
     * Template Class Name
     * @var string
     */
    public $class_name = '';

    /**
     * populate Compiled Resource Object with meta data from Resource
     *
     * @param  Smarty                       $smarty     Smarty object
     * @return void
     */
    public function populate(Smarty $smarty)
    {
        $this->filepath = $this->buildFilepath($smarty);
        $this->timestamp = @filemtime($this->filepath);
        $this->exists = !!$this->timestamp;
    }

    /**
     * load compiled template class
     *     * @return void
     */
    public function loadTemplateClass()
    {
        if ($this->exists) {
            include $this->filepath;
        }
    }

    /**
     * populate Compiled Object with compiled filepath
     *
     * @param  Smarty                       $smarty     Smarty object
     * @return string
     */
    public function buildFilepath($smarty)
    {
        $_compile_id = isset($this->compile_id) ? preg_replace('![^\w\|]+!', '_', $this->compile_id) : null;
        $_filepath = $this->source->uid . '_' . $smarty->compiletime_options;
        // if use_sub_dirs, break file into directories
        if ($smarty->use_sub_dirs) {
            $_filepath = substr($_filepath, 0, 2) . '/'
                . substr($_filepath, 2, 2) . '/'
                . substr($_filepath, 4, 2) . '/'
                . $_filepath;
        }
        $_compile_dir_sep = $smarty->use_sub_dirs ? '/' : '^';
        if (isset($_compile_id)) {
            $_filepath = $_compile_id . $_compile_dir_sep . $_filepath;
        }
        // subtype
        if ($this->source->usage == Smarty::IS_CONFIG) {
            $_subtype = '.config';
            // TODO must caching be a compiled property?
        } elseif ($this->caching) {
            $_subtype = '.cache';
        } else {
            $_subtype = '';
        }
        $_compile_dir = $smarty->getCompileDir();
        // set basename if not specified
        $_basename = $this->source->getBasename();
        if ($_basename === null) {
            $_basename = basename(preg_replace('![^\w\/]+!', '_', $this->source->name));
        }
        // separate (optional) basename by dot
        if ($_basename) {
            $_basename = '.' . $_basename;
        }

        return $_compile_dir . $_filepath . '.' . $this->source->type . $_basename . $_subtype . '.php';
    }

    /**
     * @param Smarty $tpl_obj
     * @param Smarty $parent
     * @param  int $scope_type
     * @param  null|array $data
     * @param  boolean $no_output_filter true if output filter shall nit run
     * @return string html output
     */
    public function getRenderedTemplate($tpl_obj, $parent, $scope_type = Smarty::SCOPE_LOCAL, $data = null, $no_output_filter = true)
    {
        return $this->instanceTemplate($tpl_obj, $parent)->getRenderedTemplate($scope_type, $data, $no_output_filter);
    }

    /**
     * Delete compiled template file
     *
     * @param  Smarty $smarty            Smarty instance
     * @param  string $template_resource template name
     * @param  string $compile_id        compile id
     * @param  integer $exp_time          expiration time
     * @return integer number of template files deleted
     */
    public function clear(Smarty $smarty, $template_resource, $compile_id, $exp_time,  $is_config)
    {
        $_compile_dir = $smarty->getCompileDir();
        $_compile_id = isset($compile_id) ? preg_replace('![^\w\|]+!', '_', $compile_id) : null;
        $compiletime_options = 0;
        $_dir_sep = $smarty->use_sub_dirs ? '/' : '^';
        if (isset($template_resource)) {
            $source = $smarty->_load(Smarty::SOURCE, $template_resource);
           if ($source->exists) {
                // set basename if not specified
                $_basename = $source->getBasename($source);
                if ($_basename === null) {
                    $_basename = basename(preg_replace('![^\w\/]+!', '_', $source->name));
                }
                // separate (optional) basename by dot
                if ($_basename) {
                    $_basename = '.' . $_basename;
                }
                $_resource_part_1 = $source->uid . '_' . $compiletime_options . '.' . $source->type . $_basename . '.php';
                $_resource_part_1_length = strlen($_resource_part_1);
            } else {
                return 0;
            }

            $_resource_part_2 = str_replace('.php', '.cache.php', $_resource_part_1);
            $_resource_part_2_length = strlen($_resource_part_2);
        }
        $_dir = $_compile_dir;
        if ($smarty->use_sub_dirs && isset($_compile_id)) {
            $_dir .= $_compile_id . $_dir_sep;
        }
        if (isset($_compile_id)) {
            $_compile_id_part = $_compile_dir . $_compile_id . $_dir_sep;
        }
        $_count = 0;
        try {
            $_compileDirs = new RecursiveDirectoryIterator($_dir);
            // NOTE: UnexpectedValueException thrown for PHP >= 5.3
        } catch (Exception $e) {
            return 0;
        }
        $_compile = new RecursiveIteratorIterator($_compileDirs, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($_compile as $_file) {
            if (substr($_file->getBasename(), 0, 1) == '.' || strpos($_file, '.svn') !== false)
                continue;

            $_filepath = (string)$_file;

            if ($_file->isDir()) {
                if (!$_compile->isDot()) {
                    // delete folder if empty
                    @rmdir($_file->getPathname());
                }
            } else {
                $unlink = false;
                if ((!isset($_compile_id) || strpos($_filepath, $_compile_id_part) === 0)
                    && (!isset($template_resource)
                        || (isset($_filepath[$_resource_part_1_length])
                            && substr_compare($_filepath, $_resource_part_1, -$_resource_part_1_length, $_resource_part_1_length) == 0)
                        || (isset($_filepath[$_resource_part_2_length])
                            && substr_compare($_filepath, $_resource_part_2, -$_resource_part_2_length, $_resource_part_2_length) == 0))
                ) {
                    if (isset($exp_time)) {
                        if (time() - @filemtime($_filepath) >= $exp_time) {
                            $unlink = true;
                        }
                    } else {
                        $unlink = true;
                    }
                }

                if ($unlink && @unlink($_filepath)) {
                    $_count++;
                    if ($smarty->enable_trace) {
                        // notify listeners of deleted file
                        $smarty->triggerTraceCallback('filesystem:delete', array($smarty, $path));
                    }
                }
            }
        }
       return $_count;
    }

    /**
     * Instance compiled template
     *
     * @param Smarty $smarty     Smarty object
     * @param Smarty|Smarty_Data|Smarty_Template_Class $parent     parent object
     * @returns Smarty_Template_Class
     */
    function instanceTemplate($smarty, $parent)
    {
        if ($this->class_name == '') {
            return $this->loadTemplate($smarty, $parent);
        } else {
            return new $this->class_name($smarty, $parent, $this->source);
        }

    }

    /**
     * Load compiled template
     *
     * @param Smarty $smarty     Smarty object
     * @param Smarty|Smarty_Data|Smarty_Template_Class $parent     parent object
     * @returns Smarty_Template_Class
     * @throws Smarty_Exception
     */
    public function loadTemplate($smarty, $parent)
    {
        try {
            $level = ob_get_level();
            if ($this->source->recompiled) {
                if ($smarty->debugging) {
                    Smarty_Debug::start_compile($this->source);
                }

                $compiler = Smarty_Compiler::load($smarty, $this->source, $this->caching);
                $compiler->compileTemplate();
                if ($smarty->debugging) {
                    Smarty_Debug::end_compile($this->source);
                }
                eval('?>' . $compiler->template_code->buffer);
                unset($compiler);
                if ($smarty->debugging) {
                    Smarty_Debug::end_compile($this->source);
                }
                if (class_exists($this->class_name, false)) {
                    $template_obj = new $this->class_name($smarty, $parent, $this->source);
                }
            } else {
                $isValid = false;
                if ($this->exists && !$smarty->force_compile && $this->timestamp >= $this->source->timestamp) {
                    // load existing compiled template class
                    $this->loadTemplateClass();
                    if (class_exists($this->class_name, false)) {
                        $template_obj = new $this->class_name($smarty, $parent, $this->source);
                        // existing class could got invalid
                        $isValid = $template_obj->isValid;
                    }
                }
                if (!$isValid) {
                    // we must compile from source
                    if ($smarty->debugging) {
                        Smarty_Debug::start_compile($this->source);
                    }
                    $compiler = Smarty_Compiler::load($smarty, $this->source, $this->caching);
                    $compiler->compileTemplateSource($this);
                    unset($compiler);
                    if ($smarty->debugging) {
                        Smarty_Debug::end_compile($this->source);
                    }
                    $this->loadTemplateClass($this);
                    if (class_exists($this->class_name, false)) {
                        $template_obj = new $this->class_name($smarty, $parent, $this->source);
                        $isValid = $template_obj->isValid;
                    }
                    if (!$isValid) {
                        throw new Smarty_Exception("Unable to load compiled template file '{$this->filepath}'");
                    }
                }
            }
        } catch (Exception $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
            throw new Smarty_Exception_Runtime('resource ', -1, null, null, $e);
        }
        return $template_obj;
    }

    /**
     * Delete compiled template file
     *
     * @param  Smarty $smarty            smarty object
     * @param  string $template_resource template name
     * @param  string $compile_id        compile id
     * @param  integer $exp_time          expiration time
     * @return integer number of template files deleted
     */
    public static function clearCompiledTemplate(Smarty $smarty, $template_resource, $compile_id, $exp_time)
    {
        // load cache resource and call clear
        $_compiled_resource = $smarty->_loadResource(Smarty::COMPILED, $smarty->compiled_type);
//        Smarty_Compiled_Resource::invalidLoadedCache($smarty);
        return $_compiled_resource->clear($template_resource, $compile_id, $exp_time, $smarty);

    }
}
