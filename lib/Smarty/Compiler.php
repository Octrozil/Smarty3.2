<?php

/**
 * Smarty Compiler
 * This file contains the root compiler class
 *
 * @package Compiler
 * @author  Uwe Tews
 */
/**
 * @ignore
 */

/**
 * Class Smarty_Compiler
 *
 * @package Compiler
 */
class Smarty_Compiler extends Smarty_Compiler_Code
{

    /**
     * internal flag to enable parser debugging
     *
     * @var boolean
     * @internal
     */
    public static $parserdebug = false;

    /**
     * plugin search order
     *
     * @var array
     * @internal
     */
    public static $plugin_search_order = array('function', 'block', 'compiler', 'class');

    /**
     * @param Smarty_Context $context
     * @param string         $filepath
     *
     * @return mixed
     */
    public static function  load($context, $filepath)
    {
        if ($context->_usage === Smarty::IS_CONFIG) {
            $type = 'Config';
        } else {
            $type = 'Template';
        }
        $class_names = $context->handler->compiler_class_names[$type];
        return new $class_names[0]($class_names[1], $class_names[2], $context, $filepath);
    }
}
