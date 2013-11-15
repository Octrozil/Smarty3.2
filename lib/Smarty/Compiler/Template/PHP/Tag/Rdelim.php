<?php

/**
 * Smarty Internal Plugin Compile Rdelim
 *
 * Compiles the {rdelim} tag
 *
 * @package Compiler
 * @author Uwe Tews
 */

/**
 * Smarty Internal Plugin Compile Rdelim Class
 *
 *
 * @package Compiler
 */
class Smarty_Compiler_Template_Php_Tag_Rdelim extends Smarty_Compiler_Template_Php_Tag
{

    /**
     * Compiles code for the {rdelim} tag
     *
     * This tag does output the right delimiter.
     *
     * @param  array $args array with attributes from parser
     * @param  object $compiler compiler object
     * @return string compiled code
     */
    public function compile($args, $compiler)
    {
        $_attr = $this->getAttributes($compiler, $args);
        if ($_attr['nocache'] === true) {
            $compiler->error('nocache option not allowed', $compiler->lex->taglineno);
        }
        // this tag does not return compiled code
        $compiler->has_code = true;

        $this->iniTagCode($compiler);

        $this->php("echo \$this->smarty->right_delimiter;")->newline();

        return $this->returnTagCode($compiler);
    }
}
