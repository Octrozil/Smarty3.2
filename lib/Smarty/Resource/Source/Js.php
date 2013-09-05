<?php

/**
 * Smarty Resource Source Javascript File Plugin
 *
 * @package Resource\Source
 * @author Uwe Tews
 * @author Rodney Rehm
 */

/**
 * Smarty Resource Source Javascript File Plugin
 *
 * Implements the file system as resource for Smarty Javascript templates
 *
 * @package Resource\Source
 */
class Smarty_Resource_Source_Js extends Smarty_Resource_Source_File
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
