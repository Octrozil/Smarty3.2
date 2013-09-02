<?php

/**
 * Smarty Resource Compiled Plugin
 *
 *
 * @package CompiledResources
 * @author Uwe Tews
 */

/**
 * Smarty Resource Compiled Plugin
 * Meta Data Container for Compiled Template Files
 *
 *
 */
class Smarty_Resource_Compiled  extends Smarty_Exception_Magic
{

    /**
     * compiled resource cache
     *
     * @var array
     * @internal
     */
    public static $resource_cache = array();

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
     * Load the compiled resource
     *
     * @params Smarty_Source_Resource $source source resource
     * @params mixed $compile_id  compile id
     * @params boolean $caching caching enabled ?
     * @return Smarty_Resource_Compiled
     */
    static function load(Smarty $smarty, Smarty_Resource_Source $source, $compile_id, $caching = false)
    {
        // check runtime cache
        $source_key = $source->uid;
        $compiled_key = $compile_id ? $compile_id : '#null#';
        if ($caching) {
            $compiled_key .= '#caching';
        }
        if (isset(self::$resource_cache[$source_key][$compiled_key])) {
            return self::$resource_cache[$source_key][$compiled_key];
        }

        $compiled = Smarty_Resource_Loader::load($smarty, Smarty_Resource_Loader::COMPILED);
        $compiled->source = $source;
        $compiled->compile_id = $compile_id;
        $compiled->caching = $caching;
        $compiled->populate($smarty);
        return self::$resource_cache[$source_key][$compiled_key] = $compiled;
   }

    /**
     * @param Smarty $tpl_obj
     * @params Smarty_Source_Resource $source source resource
     * @params mixed $compile_id  compile id
     * @param Smarty $parent
     * @param  int $scope_type
     * @param  null|array $data
     * @param  boolean $no_output_filter true if output filter shall nit run
     * @return string html output
     */
    static function getRenderedTemplate($tpl_obj, $source, $compile_id, $parent, $scope_type = Smarty::SCOPE_LOCAL, $data = null, $no_output_filter = true) {
        return self::load($tpl_obj, $source, $compile_id)->instanceTemplate($tpl_obj, $parent)->getRenderedTemplate($scope_type, $data, $no_output_filter);
    }

    /**
     * Instance compiled template
     *
     * @param Smarty                                    $smarty     Smarty object
     * @param Smarty|Smarty_Data|Smarty_Template_Class  $parent     parent object
     * @returns Smarty_Template_Class
     */
    function instanceTemplate($smarty, $parent) {
        if ($this->class_name == '') {
            return $this->loadTemplate($smarty, $parent);
        } else {
            return new $this->class_name($smarty, $parent, $this->source);
        }

    }
    /**
     * Load compiled template
     *
     * @param Smarty                                    $smarty     Smarty object
     * @param Smarty|Smarty_Data|Smarty_Template_Class  $parent     parent object
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
                $template_obj = new $this->class_name($smarty, $parent, $this->source);

            } else {
                $isValid = false;
                if ($this->exists && !$smarty->force_compile && $this->timestamp >= $this->source->timestamp) {
                    // load existing compiled template class
                    $this->loadTemplateClass($this);
                    $template_obj = new $this->class_name($smarty, $parent, $this->source);
                    $class_name = $this->class_name;
                    // existing class could got invalid
                    $isValid = $class_name::$isValid;
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
                    $template_obj = new $this->class_name($smarty, $parent, $this->source);
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
