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
    public $template_class_name = '';

    /**
     * Template object is valid
     * @var string
     */
    public $isValid = false;

    /**
     * Template object
     * @var Smarty_Template
     */
    public $template_obj = null;

    /**
     * populate Compiled Resource Object with meta data from Resource
     *
     * @param  Smarty $smarty     Smarty object
     * @return boolean  true if file exits
     */
    public function populate(Smarty $smarty)
    {
        $this->filepath = $this->buildFilepath($smarty);
        if (is_file($this->filepath)){
            $this->timestamp = filemtime($this->filepath);
            return $this->exists = true;
        }
        return $this->timestamp = $this->exists = false;
    }

    /**
     * populate Cached Object with timestamp and exists from Resource
     *
     * @param  Smarty $smarty     Smarty object
     * @return boolean  true if file exits
     */
    public function populateTimestamp(Smarty $tpl_obj)
    {
        if (is_file($this->filepath)){
            $this->timestamp = filemtime($this->filepath);
            return $this->exists = true;
        }
        return $this->timestamp = $this->exists = false;
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
     * @param  Smarty $smarty     Smarty object
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
        if ($this->source->_usage == Smarty::IS_CONFIG) {
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
     * Load compiled template
     *
     * @param Smarty $smarty     Smarty object
     * @param Smarty|Smarty_Data|Smarty_Template $parent     parent object
     * @returns Smarty_Template
     * @throws Smarty_Exception
     */
    public function instanceTemplate($smarty, $parent)
    {
        try {
            if ($this->isValid && isset($this->template_obj) && ($this->isCompiled || !$smarty->force_compile)) {
                return $this->template_obj;
            }
            $level = ob_get_level();
            if ($this->source->recompiled) {
                $this->template_obj = null;
                $this->template_class_name = '';
                $this->isValid = $this->isCompiled = false;
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
                if (class_exists($this->template_class_name, false)) {
                    $this->isCompiled = true;
                    $this->template_obj = new $this->template_class_name($smarty, $parent, $this->source);
                    $this->isValid = $this->template_obj->isValid;
                }
            } else {
                $this->isValid = false;
                if ($this->exists && !$smarty->force_compile && $this->timestamp >= $this->source->timestamp) {
                    $this->template_class_name = '';
                    // load existing compiled template class
                    $this->loadTemplateClass();
                    if (class_exists($this->template_class_name, false)) {
                        $this->template_obj = new $this->template_class_name($smarty, $parent, $this->source);
                        $this->isValid = $this->template_obj->isValid;
                    }
                }
                if (!$this->isValid) {
                    $this->template_class_name = '';
                    $this->template_obj = null;
                    $this->isValid = $this->isCompiled = false;
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
                    $this->isCompiled = true;
                    $this->populateTimestamp($smarty);
                    $this->loadTemplateClass($this);
                    if (class_exists($this->template_class_name, false)) {
                        $this->template_obj = new $this->template_class_name($smarty, $parent, $this->source);
                        $this->isValid = $this->template_obj->isValid;
                    }
                    if (!$this->isValid) {
                        throw new FileLoadError('compiled template', $this->filepath);
                    }
                }
            }
        } catch (Exception $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
//            throw new Smarty_Exception_Runtime('resource ', -1, null, null, $e);
            throw $e;
        }
        return $this->template_obj;
    }

    /**
     * Delete compiled template file
     *
     * @internal
     * @param  Smarty $smarty            Smarty instance
     * @param  string $template_resource template name
     * @param  string $compile_id        compile id
     * @param  integer $exp_time          expiration time
     * @return integer number of template files deleted
     */
    public function clear(Smarty $smarty, $template_resource, $compile_id, $exp_time, $is_config)
    {
        // is external to save memory
        return Smarty_Resource_Compiled_Extension_File::clear($smarty, $template_resource, $compile_id, $exp_time, $is_config);
    }
}
