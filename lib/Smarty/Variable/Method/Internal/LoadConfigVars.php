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
 * Class for internal _loadConfigVars method
 *
 * @package Smarty\Extension
 */
class Smarty_Variable_Method_Internal_LoadConfigVars
{
    /**
     *  Smarty object
     *
     * @var Smarty
     */
    public $smarty;

    /**
     *  Constructor
     *
     * @param Smarty $smarty Smarty object
     */
    public function __construct($smarty)
    {
        $this->smarty = $smarty;
    }

    /**
     * Template code runtime function to load config varibales
     *
     * @internal
     * @param Smarty_Template $template_obj
     */
    public function _loadConfigVars(Smarty_Template $template_obj)
    {
        $smarty = $template_obj->smarty;
        $target = $template_obj->parent;
        $scope =  $target->_tpl_vars;
        $scope_type = $smarty->_tpl_vars->___config_scope;
        if (isset($template_obj->config_data['vars'])) {
            foreach ($template_obj->config_data['vars'] as $var => $value) {
                if (!$smarty->config_overwrite && isset($scope->$var)) {
                    $value = array_merge((array)$scope->{$var}, (array)$value);
                }
                $target->_assignInScope($var, $value, $scope_type);
            }
        }
        if (isset($template_obj->config_data['sections'][$smarty->_tpl_vars->___config_sections])) {
            foreach ($template_obj->config_data['sections'][$smarty->_tpl_vars->___config_sections]['vars'] as $var => $value) {
                if (!$smarty->config_overwrite && isset($scope->$var)) {
                    $value = array_merge((array)$scope->{$var}, (array)$value);
                }
                $target->_assignInScope($var, $value, $scope_type);
            }
        }
    }
}