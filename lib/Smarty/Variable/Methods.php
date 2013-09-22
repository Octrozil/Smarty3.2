<?php

/**
 * Smarty Variable Methods
 *
 * This file contains the basic methods for variable handling
 *
 *
 * @package Smarty
 * @author Uwe Tews
 */

/**
 * Base class with variable methods
 *
 *
 * @package Smarty
 */
class Smarty_Variable_Methods extends Smarty_Exception_Magic
{
    /**
     * parent template (if any)
     *
     * @var Smarty
     */
    public $parent = null;

    /**
     * loaded Smarty extension objects
     *
     * @internal
     * @var array
     */
    public $_loaded_extensions = array();

    /**
     * assigns a Smarty variable
     *
     * @api
     * @param  array|string $tpl_var the template variable name(s)
     * @param  mixed $value   the value to assign
     * @param  boolean $nocache if true any output of this variable will be not cached
     * @param int $scope_type
     * @return Smarty_Variable_Methods current Smarty_Variable_Methods (or Smarty) instance for chaining
     */
    public function assign($tpl_var, $value = null, $nocache = false, $scope_type = Smarty::SCOPE_LOCAL)
    {
        if (is_array($tpl_var)) {
            foreach ($tpl_var as $varname => $value) {
                if ($varname != '') {
                    $this->_assignInScope($varname, new Smarty_Variable($value, $nocache), $scope_type);
                }
            }
        } else {
            if ($tpl_var != '') {
                $this->_assignInScope($tpl_var, new Smarty_Variable($value, $nocache), $scope_type);
            }
        }

        return $this;
    }

    /**
     * assigns a global Smarty variable
     *
     * @api
     * @param  string $varname the global variable name
     * @param  mixed $value   the value to assign
     * @param  boolean $nocache if true any output of this variable will be not cached
     * @return Smarty_Variable_Methods current Smarty_Variable_Methods (or Smarty) instance for chaining
     */
    public function assignGlobal($varname, $value = null, $nocache = false)
    {
        if ($varname != '') {
            $this->_assignInScope($varname, new Smarty_Variable($value, $nocache), Smarty::SCOPE_GLOBAL);
        }
        return $this;
    }


     /**
     * Assign variable in scope
     * bubble up if required
     *
     * @internal
     * @param string $varname variable name
     * @param Smarty_Variable $variable_obj variable object
     * @param int $scope_type
     */
    public function _assignInScope($varname, $variable_obj, $scope_type = Smarty::SCOPE_LOCAL)
    {
        if ($scope_type == Smarty::SCOPE_GLOBAL) {
            Smarty::$_global_tpl_vars->{$varname} = $variable_obj;
            if ($this->_usage == Smarty::IS_TEMPLATE) {
                // we must bubble from current template
                $scope_type = Smarty::SCOPE_ROOT;
            } else {
                // otherwise done
                return;
            }
        }

        // must always assign in local scope
        $this->_tpl_vars->{$varname} = $variable_obj;

        // if called on data object return
        if ($this->_usage == Smarty::IS_DATA) {
            return;
        }

        // if called from template object ($this->scope_type set) we must consider
        // the scope type if template object
        if (isset($this->scope_type)) {
            if ($scope_type != Smarty::SCOPE_ROOT && $scope_type != Smarty::SCOPE_SMARTY) {
                if ($this->scope_type == Smarty::SCOPE_ROOT) {
                    $scope_type = Smarty::SCOPE_ROOT;
                } else if ($scope_type == Smarty::SCOPE_LOCAL) {
                    $scope_type = $this->scope_type;
                }
            }
        }

        if ($scope_type == Smarty::SCOPE_LOCAL) {
            return;
        }

        $node = $this->parent;
        while ($node) {
            // bubble up only in template objects
            if ($node->_usage == Smarty::IS_TEMPLATE || ($scope_type == Smarty::SCOPE_SMARTY && $node->_usage != Smarty::IS_DATA)) {
                $node->_tpl_vars->{$varname} = $variable_obj;
                if ($scope_type == Smarty::SCOPE_PARENT) {
                    break;
                }
            }
            $node = $node->parent;
        }
    }

    /**
     * Handle unknown class methods
     *  - load extensions for external variable methods
     *
     * @param  string $name unknown method-name
     * @param  array $args argument array
     * @throws Smarty_Exception
     * @return mixed    function results
     */
    public function __call($name, $args)
    {

        // try extensions
        if (isset($this->_loaded_extensions[$name])) {
            return call_user_func_array(array($this->_loaded_extensions[$name], $name), $args);
        }

        $class = 'Smarty_Variable_Extension_' . (($name[0] != '_') ? ucfirst($name) : ('Internal_' . ucfirst(substr($name, 1))));
        if (class_exists($class, true)) {
            $obj = new $class($this);
            if (method_exists($obj, $name)) {
                $this->_loaded_extensions[$name] = $obj;
                return call_user_func_array(array($obj, $name), $args);
            }
        }
        throw new Smarty_Exception("Call of undefined method '{$name}'");
    }
}
