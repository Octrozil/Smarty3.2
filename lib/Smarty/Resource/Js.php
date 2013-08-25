<?php

/**
 * Smarty Internal Plugin Javascript Resource File
 *
 *
 * @package TemplateResources
 * @author Uwe Tews
 * @author Rodney Rehm
 */

/**
 * Smarty Internal Plugin Javascript Resource File
 *
 * Implements the file system as resource for Smarty Javascript templates
 *
 *
 * @package TemplateResources
 */
class Smarty_Resource_Js extends Smarty_Resource_File
{
    /**
     * Resource compiler class
     *
     */
    public $compiler_class = 'Smarty_Compiler_Template_Javascript_Compiler';

    /**
     * Resource lexer class
     *
     */
    public $lexer_class = 'Smarty_Compiler_Template_Lexer';

    /**
     * Resource lexer class
     *
     */
    public $parser_class = 'Smarty_Compiler_Template_Javascript_Parser';


}
