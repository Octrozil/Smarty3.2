<?php

/**
 * Smarty Compiled Resource Plugin
 *
 *
 * @package CompiledResources
 * @author Uwe Tews
 */

/**
 * Meta Data Container for Compiled Template Files
 *
 *
 * @property string $content compiled content
 */
class Smarty_Compiled_Resource extends Smarty_Exception_Magic
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
     * Populate compiled resource properties
     *
     * @param Smarty $tpl_obj template object
     * @params Smarty_Resource $source source resource
     * @params mixed $compile_id  compile id
     * @params boolean $caching caching enabled ?
     * @return Smarty_Compiled_Resource
     */
    public function populateResource($tpl_obj, $source, $compile_id, $caching)
    {
        $this->source = $source;
        $this->compile_id = $compile_id;
        $this->caching = $caching;
        $this->populate($tpl_obj);

        return $this;
    }


    /**
     * Load compiled template
     *
     * @param Smarty                                   $tpl_obj Smarty object
     * @param Smarty|Smarty_Data|Smarty_Template_Class $parent  parent object
     * @returns Smarty_Template_Class
     * @throws Smarty_Exception
     */
    public function loadTemplate($tpl_obj, $parent)
    {
        try {
            $level = ob_get_level();
            if ($this->source->recompiled) {
                if ($tpl_obj->debugging) {
                    Smarty_Debug::start_compile($this->source);
                }

                $compiler = Smarty_Compiler::load($tpl_obj, $this->source, $this->caching);
                $compiler->compileTemplate();
                if ($tpl_obj->debugging) {
                    Smarty_Debug::end_compile($this->source);
                }
                eval('?>' . $compiler->template_code->buffer);
                unset($compiler);
                if ($tpl_obj->debugging) {
                    Smarty_Debug::end_compile($this->source);
                }
                $template_obj = new $this->class_name($tpl_obj, $parent, $this->source);

            } else {
                $isValid = false;
                if ($this->exists && !$tpl_obj->force_compile && $this->timestamp >= $this->source->timestamp) {
                    // load existing compiled template class
                    $this->process($tpl_obj);
                        $template_obj = new $this->class_name($tpl_obj, $parent, $this->source);
                        $class_name = $this->class_name;
                        // existing class could got invalid
                        $isValid = $class_name::$isValid;
                }
                if (!$isValid) {
                    // we must compile from source
                    if ($tpl_obj->debugging) {
                        Smarty_Debug::start_compile($this->source);
                    }
                    $compiler = Smarty_Compiler::load($tpl_obj, $this->source, $this->caching);
                    $compiler->compileTemplateSource($this);
                    unset($compiler);
                    if ($tpl_obj->debugging) {
                        Smarty_Debug::end_compile($this->source);
                    }
                    $this->process($tpl_obj);
                        $template_obj = new $this->class_name($tpl_obj, $parent, $this->source);
                    $class_name = $this->class_name;
                    $isValid = $class_name::$isValid;
                    if (!$isValid) {
                        throw new Smarty_Exception("Unable to load compiled template file '{$this->filepath}");
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
     * @param  Smarty  $smarty            smarty object
     * @param  string  $template_resource template name
     * @param  string  $compile_id        compile id
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
