<?php

/**
 * Smarty Resource Compiled Recompiled Plugin
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
class Smarty_Resource_Compiled_Recompiled extends Smarty_Exception_Magic
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
     * Template object is valid
     * @var string
     */
    public $isValid = false;

    /**
     * populate Compiled Resource Object with meta data from Resource
     *
     * @param  Smarty $smarty     Smarty object
     * @return boolean  true if file exits
     */
    public function populate(Smarty $smarty)
    {
    }

    /**
     * Load compiled template
     *
     * @param Smarty $smarty     Smarty object
     * @param $source
     * @param $compile_id
     * @param $caching
     * @throws Exception
     * @returns Smarty_Template
     */
    public function instanceTemplate($smarty, $source, $compile_id, $caching)
    {
        try {
            $level = ob_get_level();
            $template_class_name = '';
            $isValid =  false;
            if ($smarty->debugging) {
                Smarty_Debug::start_compile($source);
            }

            $compiler = Smarty_Compiler::load($smarty, $source, false, $caching);
            $compiler->compileTemplate();
            if ($smarty->debugging) {
                Smarty_Debug::end_compile($source);
            }
            eval('?>' . $compiler->template_code->buffer);
            unset($compiler);
            if ($smarty->debugging) {
                Smarty_Debug::end_compile($source);
            }
            if (class_exists($template_class_name, false)) {
                $template_obj = new $template_class_name($smarty, $source, false, 0);
                $template_obj->isUpdated = true;
                $isValid = $template_obj->isValid;
            }
            if (!$isValid) {
                throw new Smarty_Exception_FileLoadError('compiled template', $source->filepath);
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
     * @param $isConfig
     * @return integer number of template files deleted
     */
    public function clear(Smarty $smarty, $template_resource, $compile_id, $exp_time, $isConfig)
    {
        // is a noop on recompiled resources
        return 0;
    }
}
