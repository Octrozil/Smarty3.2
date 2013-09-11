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
    }


    /**
     * @param Smarty $tpl_obj
     * @param Smarty $parent
     * @param  Smarty_Variable_Scope $_scope
     * @param  int $scope_type
     * @param  null|array $data
     * @param  boolean $no_output_filter true if output filter shall nit run
     * @return string html output
     */
    public function getRenderedTemplate($tpl_obj, $parent, $scope, $scope_type = Smarty::SCOPE_LOCAL, $data = null, $no_output_filter = true)
    {
        return $this->instanceTemplate($tpl_obj, $parent)->getRenderedTemplate($scope, $scope_type, $data, $no_output_filter);
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
        // is a noop on recompiled resources
        return 0;
    }
}
