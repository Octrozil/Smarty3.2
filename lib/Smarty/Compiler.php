<?php

/**
 * Smarty Compiler
 *
 * This file contains the root compiler class
 *
 *
 * @package Compiler
 * @author Uwe Tews
 */
/**
 * @ignore
 */

/**
 * Class Smarty_Compiler
 *
 *
 * @package Compiler
 */
class Smarty_Compiler extends Smarty_Compiler_Code
{
    public static function  load(Smarty $tpl_obj, $source, $caching = false)
    {
        if ($source->usage == Smarty::IS_TEMPLATE) {
            return new Smarty_Compiler_Template_PHP_Compiler('Smarty_Compiler_Template_PHP_Lexer', 'Smarty_Compiler_Template_PHP_Parser', $tpl_obj, $source, $caching);
        } else {
            return new Smarty_Compiler_Config_Compiler('Smarty_Compiler_Config_Lexer', 'Smarty_Compiler_Config_Parser', $tpl_obj);
        }
    }
}
