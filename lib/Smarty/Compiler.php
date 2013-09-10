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
        if ($source->_usage == Smarty::IS_SMARTY_TPL_CLONE) {
            $compiler = isset($source->compiler_class) ? $source->compiler_class : $tpl_obj->template_compiler_class;
            $lexer = isset($source->lexer_class) ? $source->lexer_class : $tpl_obj->template_lexer_class;
            $parser = isset($source->parser_class) ? $source->parser_class : $tpl_obj->template_parser_class;
            return new $compiler($lexer, $parser, $tpl_obj, $source, $caching);
        } else {
            $compiler = isset($source->compiler_class) ? $source->compiler_class : $tpl_obj->config_compiler_class;
            $lexer = isset($source->lexer_class) ? $source->lexer_class : $tpl_obj->config_lexer_class;
            $parser = isset($source->parser_class) ? $source->parser_class : $tpl_obj->config_parser_class;
            return new $compiler($lexer, $parser, $tpl_obj);
        }
    }
}
