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
     * populate Compiled Object with compiled filepath
     *
     * @param  Smarty_Context $context
     * @return string
     */
    public function buildFilepath(Smarty_Context $context)
    {
        $_compile_id = isset($context->compile_id) ? preg_replace('![^\w\|]+!', '_', $context->compile_id) : null;
        $_filepath = $context->uid . '_' . $context->smarty->compiletime_options;
        // if use_sub_dirs, break file into directories
        if ($context->smarty->use_sub_dirs) {
            $_filepath = substr($_filepath, 0, 2) . '/'
                . substr($_filepath, 2, 2) . '/'
                . substr($_filepath, 4, 2) . '/'
                . $_filepath;
        }
        $_compile_dir_sep = $context->smarty->use_sub_dirs ? '/' : '^';
        if (isset($_compile_id)) {
            $_filepath = $_compile_id . $_compile_dir_sep . $_filepath;
        }
        // subtype
        if ($context->_usage == Smarty::IS_CONFIG) {
            $_subtype = '.config';
            // TODO must caching be a compiled property?
        } elseif ($context->caching) {
            $_subtype = '.cache';
        } else {
            $_subtype = '';
        }
        $_compile_dir = $context->smarty->getCompileDir();
        // set basename if not specified
        $_basename = $context->handler->getBasename($context);
        if ($_basename === null) {
            $_basename = basename(preg_replace('![^\w\/]+!', '_', $context->name));
        }
        // separate (optional) basename by dot
        if ($_basename) {
            $_basename = '.' . $_basename;
        }

        return $_compile_dir . $_filepath . '.' . $context->type . $_basename . $_subtype . '.php';
    }

    /**
     * get timestamp and exists from Resource
     *
     * @param  Smarty $smarty     Smarty object
     * @param  string $filepath
     * @param  reference integer $timestamp
     * @param  reference boolean $exists
     */
    public function populateTimestamp(Smarty $smarty, $filepath, &$timestamp, &$exists)
    {
        if ($filepath && is_file($filepath)) {
            $timestamp = filemtime($filepath);
            $exists = true;
        } else {
            $timestamp = $exists = false;
        }
    }

    /**
     * load compiled template class
     *
     * @param string $filepath
     * @return string  template class name
     */
    public function loadTemplateClass($filepath)
    {
        $template_class_name = '';
        include $filepath;
        return $template_class_name;
    }

    /**
     * Load compiled template
     *
     * @param Smarty_Context $context
     * @throws Exception
     * @returns Smarty_Template
     */
    public function instanceTemplate(Smarty_Context $context)
    {
        $timestamp = $exists = false;
        $filepath = $this->buildFilepath($context);
        $this->populateTimestamp($context->smarty, $filepath, $timestamp, $exists);

        try {
            $level = ob_get_level();
            $isValid = false;
            if ($exists && !$context->smarty->force_compile && $timestamp >= $context->timestamp) {
                $template_class_name = '';
                // load existing compiled template class
                $template_class_name = $this->loadTemplateClass($filepath);
                if (class_exists($template_class_name, false)) {
                    $template_obj = new $template_class_name($context, $filepath, $timestamp);
                    $isValid = $template_obj->isValid;
                }
            }
            if (!$isValid) {
                $template_class_name = '';
                // we must compile from source
                if ($context->smarty->debugging) {
                    Smarty_Debug::start_compile($context);
                }
                $compiler = Smarty_Compiler::load($context, $filepath);
                $compiler->compileTemplateSource();
                unset($compiler);
                if ($context->smarty->debugging) {
                    Smarty_Debug::end_compile($context);
                }
                $this->populateTimestamp($context->smarty, $filepath, $timestamp, $exists);
                $template_class_name = $this->loadTemplateClass($filepath);
                if (class_exists($template_class_name, false)) {
                    $template_obj = new $template_class_name($context, $filepath, $timestamp);
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
     * @param  boolean $isConfig
     * @return integer number of template files deleted
     */
    public function clear(Smarty $smarty, $template_resource, $compile_id, $exp_time, $isConfig)
    {
        // is external to save memory
        return Smarty_Resource_Compiled_Extension_File::clear($smarty, $template_resource, $compile_id, $exp_time, $isConfig);
    }
}
