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
 * @package Template
 */
class Smarty_Data extends Smarty_Variable_Methods
{

    public $tpl_vars = null;

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
        $this->usage = Smarty::IS_DATA;

        // variables passed as array?
        if (is_array($parent)) {
            $data = $parent;
            $parent = null;
        } else {
            $data = null;
        }

        // create variabale container
        $this->tpl_vars = new Smarty_Variable_Scope($smarty, $parent, Smarty::IS_DATA, $scope_name);

        //load optional variable array
        if (isset($data)) {
            foreach ($data as $_key => $_val) {
                $this->tpl_vars->$_key = new Smarty_Variable($_val);
            }
        }
    }

}
