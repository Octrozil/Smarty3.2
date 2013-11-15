<?php
/**
 * Smarty Template Scope
 *
 * This file contains the Class for a template scope
 *
 *
 * @package Smarty\Template
 * @author Uwe Tews
 */

/**
 * class for a template scope
 *
 * This class holds scope variables while rendering template
 *
 */
class Smarty_Template_Scope //extends Smarty_Exception_Magic
{
    /**
     * Local variable scope
     * @var Smarty_Variable_Scope
     */
    public $_tpl_vars = null;

    /**
     * parent
     *
     * @var Smarty  | Smarty_Data | Smarty_Template
     */
    public $parent = null;

    /**
     * merged template functions
     * @var array
     */
    public $template_functions = array();


    /**
     * Initialize template scope
     *
     * @param Smarty_Context $context
     */
    public function __construct(Smarty_Context $context)
    {
        if ($context->scope_type == Smarty::SCOPE_NONE) {
            $this->_tpl_vars = new Smarty_Variable_Scope();
        } else {
            if ($context->parent instanceof Smarty_Template) {
                $this->_tpl_vars = clone $context->parent->_tpl_vars;
            } else {
                if ($context->parent == null) {
                    $this->_tpl_vars = clone $context->smarty->_tpl_vars;
                } else {
                    $this->_tpl_vars = $this->_mergeScopes($context->parent);
                    foreach ($context->smarty->_tpl_vars as $var => $obj) {
                        $this->_tpl_vars->$var = $obj;
                    }
                }
                // merge global variables
                foreach (Smarty::$_global_tpl_vars as $var => $obj) {
                    if (!isset($this->_tpl_vars->$var)) {
                        $this->_tpl_vars->$var = $obj;
                    }
                }
            }
        }
        $this->template_functions = $context->smarty->template_functions;
    }

    /**
     *
     *  merge recursively template variables into one scope
     *
     * @internal
     * @param   Smarty|Smarty_Data|Smarty_Template $ptr
     * @return Smarty_Variable_Scope    merged tpl vars
     */
    public function _mergeScopes($ptr)
    {
        // Smarty::triggerTraceCallback('trace', ' merge tpl ');

        if (isset($ptr->parent)) {
            $scope = $this->_mergeScopes($ptr->parent);
            foreach ($ptr->_tpl_vars as $var => $obj) {
                $scope->$var = $obj;
            }

            return $scope;
        } else {
            return clone $ptr->_tpl_vars;
        }
    }
}
