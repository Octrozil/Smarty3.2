<?php

/**
 * Smarty Data
 *
 * This file contains the Smarty Data Class
 *
 *
 * @package Template
 * @author Uwe Tews
 */

/**
 * class for the Smarty data object
 *
 * The Smarty data object will hold Smarty variables in the current scope
 *
 *
 * @package Smarty
 */
class Smarty_Data extends Smarty_Variable_Methods
{
    /**
     * assigned template vars
     *
     * @internal
     * @var Smarty_Variable_Scope
     */
    public $_tpl_vars = null;
    /**
     * Declare the type template variable storage
     *
     * @internal
     * @var Smarty::IS_DATA
     */
    public $_usage = Smarty::IS_DATA;
    /**
     * Smarty Object
     *
     * @var Smarty
     */
    public $smarty = null;
    /**
     * Name of data Object
     *
     * @var string
     */
    public $scope_name = null;

    /**
     * create Smarty data object
     *
     * @param  Smarty $smarty     object of Smarty instance
     * @param  Smarty_Variable_Methods|array $parent     parent object or variable array
     * @param  string $scope_name name of variable scope
     * @throws Smarty_Exception
     */
    public function __construct(Smarty $smarty, $parent = null, $scope_name = 'Data unnamed')
    {
        // variables passed as array?
        if (is_array($parent)) {
            $data = $parent;
            $parent = null;
        } else {
            $data = null;
        }

        $this->smarty = $smarty;
        $this->scope_name = $scope_name;
        $this->parent = $parent;

        // create variabale container
        $this->_tpl_vars = new Smarty_Variable_Scope();

        //load optional variable array
        if (isset($data)) {
            foreach ($data as $_key => $_val) {
                $this->_tpl_vars->$_key = new Smarty_Variable($_val);
            }
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
        if (isset($this->_autoloaded[$name])) {
            return call_user_func_array(array($this->_autoloaded[$name], $name), $args);
        }

        $class = ($name[0] != '_') ? 'Smarty_Variable_Method_' . ucfirst($name) : ('Smarty_Variable_Internal_' . ucfirst(substr($name, 1)));
        if (class_exists($class, true)) {
            $obj = new $class($this);
            if (method_exists($obj, $name)) {
                $this->_autoloaded[$name] = $obj;
                return call_user_func_array(array($obj, $name), $args);
            }
        }
        // throw error through magic parent
        Smarty_Exception_Magic::__call($name, $args);
    }
}
