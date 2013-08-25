<?php

/**
 * Smarty Internal Plugin Compile Debug
 *
 * Compiles the {debug} tag.
 * It opens a window the the Smarty Debugging Console.
 *
 *
 * @package Compiler
 * @author Uwe Tews
 */

/**
 * Smarty Internal Plugin Compile Debug Class
 *
 *
 * @package Compiler
 */
class Smarty_Compiler_Template_Javascript_Tag_Debug extends Smarty_Compiler_Template_Javascript_Tag
{

    /**
     * Compiles code for the {debug} tag
     *
     * @param  array  $args     array with attributes from parser
     * @param  object $compiler compiler object
     * @return string compiled code
     */
    public function compile($args, $compiler)
    {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);

        // compile always as nocache
        $compiler->tag_nocache = true;

        $this->iniTagCode($compiler);

        $this->php("Smarty_Debug::display_debug(\$_smarty_tpl);")->newline();

        return $this->returnTagCode($compiler);
    }

}
