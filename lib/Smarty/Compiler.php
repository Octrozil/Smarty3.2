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

    /**
     * internal flag to enable parser debugging
     * @var boolean
     * @internal
     */
    public static $parserdebug = false;

    /**
     * plugin search order
     * @var array
     * @internal
     */
    public static $plugin_search_order = array('function', 'block', 'compiler', 'class');

    public static function  load(Smarty $smarty, $source, $filepath, $caching = false)
    {
        if ($source->_usage !== Smarty::IS_CONFIG) {
            return new $source->handler->template_compiler_class($source->handler->template_lexer_class, $source->handler->template_parser_class, $smarty, $source, $filepath, $caching);
        } else {
            return new $source->handler->config_compiler_class($source->handler->config_lexer_class, $source->handler->config_parser_class, $smarty, $source, $filepath);
        }
    }
}
