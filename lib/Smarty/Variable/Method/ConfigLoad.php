<?php

/**
 * Smarty Extension
 *
 * Smarty class methods
 *
 * @package Smarty\Extension
 * @author Uwe Tews
 */

/**
 * Class for static configLoad method
 *
 * @package Smarty\Extension
 */
class Smarty_Variable_Method_ConfigLoad
{
    /**
     *  Master object
     *
     * @var Smarty | Smarty_Data | Smarty_Template
     */
    public $object;

    /**
     *  Constructor
     *
     * @param Smarty | Smarty_Data | Smarty_Template $object master object
     */
    public function __construct($object)
    {
        $this->object = $object;
    }


    /**
     * load a config file, optionally load just selected sections
     *
     * @api
     * @param  string $config_file filename
     * @param  mixed $sections array of section names, single section or null
     * @param int $scope_type template scope into which config file shall be loaded
     * @throws Smarty_Exception_SourceNotFound
     * @return Smarty_Variable_Methods current Smarty_Variable_Methods (or Smarty) instance for chaining
     */
    public function configLoad($config_file, $sections = null, $scope_type = Smarty::SCOPE_LOCAL)
    {
        $smarty = isset($this->object->smarty) ? $this->object->smarty : $this->object;
        // parse template_resource into name and type
        $parts = explode(':', $config_file, 2);
        if (!isset($parts[1]) || !isset($parts[0][1])) {
            // no resource given, use default
            // or single character before the colon is not a resource type, but part of the filepath
            $type = $smarty->default_resource_type;
            $name = $config_file;
        } else {
            $type = $parts[0];
            $name = $parts[1];
        }
        $context = new Smarty_Context($smarty, $name, $type, $this->object, true);
        // checks if source exists
        if (!$context->exists) {
            throw new Smarty_Exception_SourceNotFound($context->type, $context->name);
        }
        // create template object without caching it
        $template_obj = $context->_getTemplateObject(Smarty::COMPILED, true);
        $target = $this->object;
        $scope = $target->_tpl_vars;
        // load global variables
        if (isset($template_obj->config_data['vars'])) {
            foreach ($template_obj->config_data['vars'] as $var => $value) {
                if (!$smarty->config_overwrite && isset($scope->$var)) {
                    $value = array_merge((array)$scope->{$var}, (array)$value);
                }
                if ($target->_usage == Smarty::IS_TEMPLATE || $scope_type != Smarty::SCOPE_LOCAL) {
                    $target->_assignInScope($var, $value, $scope_type);
                } else {
                    $target->_tpl_vars->$var = $value;
                }
            }
        }
        // load variables from section
        if (isset($sections)) {
            foreach ((array)$sections as $section) {
                if (isset($template_obj->config_data['sections'][$section])) {
                    foreach ($template_obj->config_data['sections'][$section]['vars'] as $var => $value) {
                        if (!$smarty->config_overwrite && isset($scope->$var)) {
                            $value = array_merge((array)$scope->{$var}, (array)$value);
                        }
                        if ($target->_usage == Smarty::IS_TEMPLATE || $scope_type != Smarty::SCOPE_LOCAL) {
                            $target->_assignInScope($var, $value, $scope_type);
                        } else {
                            $target->_tpl_vars->$var = $value;
                        }
                    }
                }
            }
        }
        return $this->object;
    }
}