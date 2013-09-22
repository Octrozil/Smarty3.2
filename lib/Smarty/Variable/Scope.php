<?php
/**
 * Smarty Variable Scope
 *
 * This file contains the Class for a variable scope
 *
 *
 * @package Template
 * @author Uwe Tews
 */

/**
 * class for a variable scope
 *
 * This class holds all assigned variables
 * The special property ___attributes is used to store control information
 *
 */
class Smarty_Variable_Scope
{

    /**
     * constructor
     */
    public function __construct()
    {
    }

    /**
     * Set a variable in a variable without checking clone status
     *
     * @param  string $varname name of variable
     * @param  Smarty_Variable $data variable object
     */
//    public function setVariable($varname, $data) {
//        $this->$varname = $data;
//    }

    /**
     * magic __get function called at access of unknown or global variable
     *
     * @param  string $varname name of variable
     * @return mixed  Smarty_Variable object | null
     */
    public function __get($varname)
    {
        //get variable from default handler
        $var = Smarty_Variable_Extension_DefaultVariableHandler::getDefaultVariable(Smarty_Template::$call_stack[0][0]->smarty, $varname);
        //assign value and bubble up if necessary
        Smarty_Template::$call_stack[0][0]->_assignInScope($varname, $var);
        return $var;
    }

     /**
    public function __destruct()
    {
    }
     */

}
