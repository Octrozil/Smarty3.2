<?php

/**
 * Smarty Internal Plugin Compile If
 *
 * Compiles the {if} {else} {elseif} {/if} tags
 *
 *
 * @package Compiler
 * @author Uwe Tews
 */

/**
 * Smarty Internal Plugin Compile If Class
 *
 *
 * @package Compiler
 */
class Smarty_Compiler_Template_Php_Tag_If extends Smarty_Compiler_Template_Php_Tag
{

    /**
     * Compiles code for the {if} tag
     *
     * @param  array $args      array with attributes from parser
     * @param  object $compiler  compiler object
     * @param  array $parameter array with compilation parameter
     * @return string compiled code
     */
    public function compile($args, $compiler, $parameter)
    {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);
        $this->openTag($compiler, 'if', array(1, $compiler->nocache));
        // must whole block be nocache ?
        $compiler->nocache = $compiler->nocache | $compiler->tag_nocache;

        if (!array_key_exists("if condition", $parameter)) {
            $compiler->error("missing if condition", $compiler->lex->taglineno);
        }

        $this->iniTagCode($compiler);

        if (is_array($parameter['if condition'])) {
            if (is_array($parameter['if condition']['var'])) {
                $var = trim($parameter['if condition']['var']['var'], "'");
            } else {
                $var = trim($parameter['if condition']['var'], "'");
            }
            if ($compiler->nocache) {
                $_nocache = 'true';
                // create nocache var to make it know for further compiling
                $compiler->tpl_obj->_tpl_vars->$var = new Smarty_Variable(null, true);
            } else {
                $_nocache = 'false';
            }
            if (is_array($parameter['if condition']['var'])) {
                $this->php("if (!isset(\$_scope->{$var}) || !is_array(\$_scope->{$var}->value)) {")->newline()->indent();
                $this->php("\$this->_createLocalArrayVariable('" . $parameter['if condition']['var']['var'] . "', \$_scope, {$_nocache});")->newline();
                $this->outdent()->php("}")->newline();
                $this->php("if (\$_scope->{$var}->value" . $parameter['if condition']['var']['smarty_internal_index'] . " = " . $parameter['if condition']['value'] . ") {")->newline()->indent();
            } else {
                $this->php("if (!isset(\$_scope->{$var})) {")->newline()->indent();
                $this->php("\$_scope->{$var} = new Smarty_Variable (null, {$_nocache});")->newline();
                $this->outdent()->php("}")->newline();
                $this->php("if (\$_scope->{$var}->value = " . $parameter['if condition']['value'] . ") {")->newline()->indent();
            }
        } else {
            $this->php("if ({$parameter['if condition']}) {")->newline()->indent();
        }

        return $this->returnTagCode($compiler);
    }

}

/**
 * Smarty Internal Plugin Compile Else Class
 *
 *
 * @package Compiler
 */
class Smarty_Compiler_Template_Php_Tag_Else extends Smarty_Compiler_Template_Php_Tag
{

    /**
     * Compiles code for the {else} tag
     *
     * @param  array $args      array with attributes from parser
     * @param  object $compiler  compiler object
     * @param  array $parameter array with compilation parameter
     * @return string compiled code
     */
    public function compile($args, $compiler, $parameter)
    {
        list($nesting, $compiler->tag_nocache) = $this->closeTag($compiler, array('if', 'elseif'));
        $this->openTag($compiler, 'else', array($nesting, $compiler->tag_nocache));

        $this->iniTagCode($compiler);

        $this->outdent()->php("} else {")->newline()->indent();

        return $this->returnTagCode($compiler);
    }

}

/**
 * Smarty Internal Plugin Compile ElseIf Class
 *
 *
 * @package Compiler
 */
class Smarty_Compiler_Template_Php_Tag_Elseif extends Smarty_Compiler_Template_Php_Tag
{

    /**
     * Compiles code for the {elseif} tag
     *
     * @param  array $args      array with attributes from parser
     * @param  object $compiler  compiler object
     * @param  array $parameter array with compilation parameter
     * @return string compiled code
     */
    public function compile($args, $compiler, $parameter)
    {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);

        list($nesting, $compiler->tag_nocache) = $this->closeTag($compiler, array('if', 'elseif'));

        if (!array_key_exists("if condition", $parameter)) {
            $compiler->error("missing elseif condition", $compiler->lex->taglineno);
        }

        if (is_array($parameter['if condition'])) {
            $condition_by_assign = true;
            if (is_array($parameter['if condition']['var'])) {
                $var = trim($parameter['if condition']['var']['var'], "'");
            } else {
                $var = trim($parameter['if condition']['var'], "'");
            }
            if ($compiler->nocache) {
                $_nocache = 'true';
                // create nocache var to make it know for further compiling
                $compiler->tpl_obj->_tpl_vars->$var = new Smarty_Variable(null, true);
            } else {
                $_nocache = 'false';
            }
        } else {
            $condition_by_assign = false;
        }

        $this->iniTagCode($compiler);

        if (empty($compiler->prefix_code)) {
            if ($condition_by_assign) {
                $this->openTag($compiler, 'elseif', array($nesting + 1, $compiler->tag_nocache));
                if (is_array($parameter['if condition']['var'])) {
                    $this->outdent()->php("} else {")->newline()->indent();
                    $this->php("if (!isset(\$_scope->{$var}) || !is_array(\$_scope->{$var}->value)) {")->newline()->indent();
                    $this->php("\$this->_createLocalArrayVariable('" . $parameter['if condition']['var']['var'] . "', \$_scope, {$_nocache});")->newline();
                    $this->outdent()->php("}")->newline();
                    $this->php("if (\$_scope->{$var}->value" . $parameter['if condition']['var']['smarty_internal_index'] . " = " . $parameter['if condition']['value'] . ") {")->newline()->indent();
                } else {
                    $this->outdent()->php("} else {")->newline()->indent();
                    $this->php("if (!isset(\$_scope->{$var})) {")->newline()->indent();
                    $this->php("\$_scope->{$var} = new Smarty_Variable (null, {$_nocache});")->newline();
                    $this->outdent()->php("}")->newline();
                    $this->php("if (\$_scope->{$var}->value = " . $parameter['if condition']['value'] . ") {")->newline()->indent();
                }
            } else {
                $this->openTag($compiler, 'elseif', array($nesting, $compiler->tag_nocache));
                $this->outdent()->php("} elseif ({$parameter['if condition']}) {")->newline()->indent();
            }
        } else {
            $this->openTag($compiler, 'elseif', array($nesting + 1, $compiler->tag_nocache));
            if ($condition_by_assign) {
                if (is_array($parameter['if condition']['var'])) {
                    $this->outdent()->php("} else {")->newline()->indent();
                    foreach ($compiler->prefix_code as $prefix_code) {
                        $this->mergeCode($prefix_code);
                    }
                    $compiler->prefix_code = array();
                    $this->php("if (!isset(\$_scope->{$var}) || !is_array(\$_scope->{$var}->value)) {")->newline()->indent();
                    $this->php("\$this->_createLocalArrayVariable('" . $parameter['if condition']['var']['var'] . "', \$_scope, {$_nocache});")->newline();
                    $this->outdent()->php("}")->newline();
                    $this->php("if (\$_scope->{$var}->value" . $parameter['if condition']['var']['smarty_internal_index'] . " = " . $parameter['if condition']['value'] . ") {")->newline()->indent();
                } else {
                    $this->outdent()->php("} else {")->newline()->indent();
                    foreach ($compiler->prefix_code as $prefix_code) {
                        $this->mergeCode($prefix_code);
                    }
                    $compiler->prefix_code = array();
                    $this->php("if (!isset(\$_scope->{$var})) {")->newline()->indent();
                    $this->php("\$_scope->{$var} = new Smarty_Variable (null, {$_nocache});")->newline();
                    $this->outdent()->php("}")->newline();
                    $this->php("if (\$_scope->{$var}->value = " . $parameter['if condition']['value'] . ") {")->newline()->indent();
                }
            } else {
                $this->outdent()->php("} else {")->newline()->indent();
                foreach ($compiler->prefix_code as $prefix_code) {
                    $this->mergeCode($prefix_code);
                }
                $compiler->prefix_code = array();
                $this->php("if ({$parameter['if condition']}) {")->newline()->indent();
            }
        }

        return $this->returnTagCode($compiler);
    }

}

/**
 * Smarty Internal Plugin Compile Ifclose Class
 *
 *
 * @package Compiler
 */
class Smarty_Compiler_Template_Php_Tag_Ifclose extends Smarty_Compiler_Template_Php_Tag
{

    /**
     * Compiles code for the {/if} tag
     *
     * @param  array $args      array with attributes from parser
     * @param  object $compiler  compiler object
     * @param  array $parameter array with compilation parameter
     * @return string compiled code
     */
    public function compile($args, $compiler, $parameter)
    {
        // must endblock be nocache?
        if ($compiler->nocache) {
            $compiler->tag_nocache = true;
        }
        list($nesting, $compiler->nocache) = $this->closeTag($compiler, array('if', 'else', 'elseif'));

        $this->iniTagCode($compiler);

        for ($i = 0; $i < $nesting; $i++) {
            $this->outdent()->php("}")->newline();
        }

        return $this->returnTagCode($compiler);
    }

}
