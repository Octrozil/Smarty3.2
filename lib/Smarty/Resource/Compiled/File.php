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
     * populate Compiled Resource Object with meta data from Resource
     *
     * @param  Smarty $smarty     Smarty object
     * @return boolean  true if file exits
     */
    public function populate(Smarty $smarty)
    {
        $this->filepath = $this->buildFilepath($smarty);
        if (is_file($this->filepath)) {
            $this->timestamp = filemtime($this->filepath);
            return $this->exists = true;
        }
        return $this->timestamp = $this->exists = false;
    }

    /**
     * get timestamp and exists from Resource
     *
     * @param  Smarty $smarty     Smarty object
     * @return boolean  true if file exits
     */
    public function populateTimestamp(Smarty $smarty, $filepath, &$timestamp, &$exists)
    {
        if (is_file($filepath)) {
            $timestamp = filemtime($filepath);
            $exists = true;
        } else {
            $timestamp = $exists = false;
        }
    }

    /**
     * load compiled template class
     *     * @return void
     */
    public function loadTemplateClass($filepath)
    {
            include $filepath;
            return $template_class_name;
    }

    /**
     * populate Compiled Object with compiled filepath
     *
     * @param  Smarty $smarty     Smarty object
     * @return string
     */
    public function buildFilepath($smarty, $source, $compile_id, $caching)
    {
        $_compile_id = isset($compile_id) ? preg_replace('![^\w\|]+!', '_', $compile_id) : null;
        $_filepath = $source->uid . '_' . $smarty->compiletime_options;
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
        if ($source->_usage == Smarty::IS_CONFIG) {
            $_subtype = '.config';
            // TODO must caching be a compiled property?
        } elseif ($caching) {
            $_subtype = '.cache';
        } else {
            $_subtype = '';
        }
        $_compile_dir = $smarty->getCompileDir();
        // set basename if not specified
        $_basename = $source->handler->getBasename($source);
        if ($_basename === null) {
            $_basename = basename(preg_replace('![^\w\/]+!', '_', $source->name));
        }
        // separate (optional) basename by dot
        if ($_basename) {
            $_basename = '.' . $_basename;
        }

        return $_compile_dir . $_filepath . '.' . $source->type . $_basename . $_subtype . '.php';
    }

    /**
     * Load compiled template
     *
     * @param Smarty $smarty     Smarty object
     * @returns Smarty_Template
     * @throws Smarty_Exception
     */
    public function instanceTemplate($smarty, $source, $compile_id, $caching)
    {
        $timestamp = $exists = false;
        $filepath = $this->buildFilepath($smarty, $source, $compile_id, $caching);
        $this->populateTimestamp($smarty, $filepath, $timestamp, $exists);

        try {
           $level = ob_get_level();
            $isValid = false;
            if ($exists && !$smarty->force_compile && $timestamp >= $source->timestamp) {
                $template_class_name = '';
                // load existing compiled template class
                $template_class_name = $this->loadTemplateClass($filepath);
                if (class_exists($template_class_name, false)) {
                    $template_obj = new $template_class_name($smarty, $source, $filepath, $timestamp);
                    $isValid = $template_obj->isValid;
                }
            }
            if (!$isValid) {
                $template_class_name = '';
                // we must compile from source
                if ($smarty->debugging) {
                    Smarty_Debug::start_compile($source);
                }
                $compiler = Smarty_Compiler::load($smarty, $source, $filepath, $caching);
                $compiler->compileTemplateSource();
                unset($compiler);
                if ($smarty->debugging) {
                    Smarty_Debug::end_compile($source);
                }
                $this->populateTimestamp($smarty, $filepath, $timestamp, $exists);
                $template_class_name = $this->loadTemplateClass($filepath);
                if (class_exists($template_class_name, false)) {
                    $template_obj = new $template_class_name($smarty, $source, $filepath, $timestamp);
                    $template_obj->isUpdated = true;
                    $isValid = $template_obj->isValid;
                }
                if (!$isValid) {
                    throw new Smarty_Exception_FileLoadError('compiled template', $filepath);
                }
            }
        } catch (Exception $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
//            throw new Smarty_Exception_Runtime('resource ', -1, null, null, $e);
            throw $e;
        }
        return $template_obj;
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
