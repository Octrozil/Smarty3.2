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
 * Class for  createData method
 *
 * @package Smarty\Extension
 */
class Smarty_Method_CreateData
{
    /**
     * creates a data object
     *
     * @api
     * @param Smarty $smarty smarty object
     * @param  Smarty|Smarty_Data|Smarty_Variable_Scope $parent next higher level of Smarty variables
     * @param  string $name optional name of Smarty_Data object
     * @return object                                   Smarty_Data
     */
    public function createData(Smarty $smarty, $parent = null, $name = 'Data unnamed')
    {
        return new Smarty_Data($smarty, $parent, $name);
    }
}
