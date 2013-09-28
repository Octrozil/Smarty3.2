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
     * load a config file, optionally load just selected sections
     *
     * @api
     * @param  string $config_file filename
     * @param  mixed $sections    array of section names, single section or null
     * @param  string $scope_type  template scope into which config file shall be loaded
     * @return Smarty_Variable_Methods current Smarty_Variable_Methods (or Smarty) instance for chaining
     */
    public function configLoad($config_file, $sections = null, $scope_type = Smarty::SCOPE_LOCAL)
    {
        $smarty = isset($this->smarty->smarty) ? $this->smarty->smarty : $this->smarty;
        $tpl_obj = $smarty->createTemplate($config_file, null, null, $this->smarty, true);
        $tpl_obj->_tpl_vars->___config_sections = $sections;
        $tpl_obj->_tpl_vars->___config_scope = $scope_type;
        $tpl_obj->source->_getRenderedTemplate($tpl_obj, Smarty::COMPILED, $tpl_obj->parent, null, null, false, 0, null, $scope_type);
        return $this->smarty;
    }
}