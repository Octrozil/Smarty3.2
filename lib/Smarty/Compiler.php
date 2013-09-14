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
    public static function  load(Smarty $smarty, $source, $filepath, $caching = false)
    {
        if ($source->_usage !== Smarty::IS_CONFIG) {
            $compiler = isset($source->handler->compiler_class) ? $source->handler->compiler_class : $smarty->template_compiler_class;
            $lexer = isset($source->handler->lexer_class) ? $source->handler->lexer_class : $smarty->template_lexer_class;
            $parser = isset($source->handler->parser_class) ? $source->handler->parser_class : $smarty->template_parser_class;
            return new $compiler($lexer, $parser, $smarty, $source, $filepath, $caching);
        } else {
            $compiler = isset($source->handler->config_compiler_class) ? $source->handler->config_compiler_class : $smarty->config_compiler_class;
            $lexer = isset($source->handler->config_lexer_class) ? $source->handler->config_lexer_class : $smarty->config_lexer_class;
            $parser = isset($source->handler->config_parser_class) ? $source->handler->config_parser_class : $smarty->config_parser_class;
            return new $compiler($lexer, $parser, $smarty, $source, $filepath);
        }
    }
}
