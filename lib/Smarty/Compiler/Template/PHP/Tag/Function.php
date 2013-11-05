<?php

/**
 * Smarty Internal Plugin Compile Function
 *
 * Compiles the {function} {/function} tags
 *
 *
 * @package Compiler
 * @author Uwe Tews
 */

/**
 * Smarty Internal Plugin Compile Function Class
 *
 *
 * @package Compiler
 */
class Smarty_Compiler_Template_Php_Tag_Function extends Smarty_Compiler_Template_Php_Tag
{

    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see $tpl_obj
     */
    public $required_attributes = array('name');

    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see $tpl_obj
     */
    public $shorttag_order = array('name');

    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see $tpl_obj
     */
    public $optional_attributes = array('_any');

    /**
     * Compiles code for the {function} tag
     *
     * @param  array $args      array with attributes from parser
     * @param  object $compiler  compiler object
     * @param  array $parameter array with compilation parameter
     * @return boolean true
     */
    public function compile($args, $compiler, $parameter)
    {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);

        if ($_attr['nocache'] === true) {
            $compiler->error('nocache option not allowed', $compiler->lex->taglineno);
        }
        unset($_attr['nocache']);
        $this->openTag($compiler, 'function', array($_attr, $compiler->template_code, $compiler->has_nocache_code, $compiler->lex->taglineno, $compiler->required_plugins));

        $compiler->template_code = new Smarty_Compiler_Code(2);

        $compiler->compiles_template_function = true;
        $compiler->has_nocache_code = false;
        $compiler->has_code = false;

        return true;
    }

}

/**
 * Smarty Internal Plugin Compile Functionclose Class
 *
 *
 * @package Compiler
 */
class Smarty_Compiler_Template_Php_Tag_Functionclose extends Smarty_Compiler_Template_Php_Tag
{

    /**
     * Compiles code for the {/function} tag
     *
     * @param  array $args      array with attributes from parser
     * @param  object $compiler  compiler object
     * @param  array $parameter array with compilation parameter
     * @return boolean true
     */
    public function compile($args, $compiler, $parameter)
    {
        $_attr = $this->getAttributes($compiler, $args);

        $saved_data = $this->closeTag($compiler, array('function'));
        $_name = trim($saved_data[0]['name'], "'\"");
        unset($saved_data[0]['name']);
        // set flag that we are compiling a template function
        $compiler->template_functions[$_name]['parameter'] = array();
//        $this->smarty = $compiler->context->smarty;
        foreach ($saved_data[0] as $_key => $_data) {
            eval('$tmp=' . $_data . ';');
            $compiler->template_functions[$_name]['parameter'][$_key] = $tmp;
        }
        // if caching save template function for possible nocache call
        if ($compiler->context->caching) {
            if (!empty($compiler->called_template_functions)) {
                $compiler->template_functions[$_name]['called_functions'] = $compiler->called_template_functions;
                $compiler->called_template_functions = array();
            }
            $plugins = array();
            foreach ($compiler->required_plugins['compiled'] as $plugin => $tmp) {
                if (!isset($saved_data[4]['compiled'][$plugin])) {
                    foreach ($tmp as $data) {
                        $plugins[$data['file']] = $data['function'];
                    }
                }
            }
            if (!empty($plugins)) {
                $compiler->template_functions[$_name]['used_plugins'] = $plugins;
            }
        }

        if ($compiler->context->type == 'eval' || $compiler->context->type == 'string') {
            $resource = $compiler->context->type;
        } else {
            $resource = $compiler->context->smarty->template_resource;
            // santitize extends resource
            if (strpos($resource, 'extends:') !== false) {
                $start = strpos($resource, ':');
                $end = strpos($resource, '|');
                $resource = substr($resource, $start + 1, $end - $start - 1);
            }
        }

        $code = new Smarty_Compiler_Code(1);
        $code->php("function _renderTemplateFunction_{$_name}(\$_scope, \$params) {")->newline()->indent();
        $code->addSourceLineNo($saved_data[3]);
        $code->php("foreach (\$params as \$key => \$value) {")->newline()->indent();
        $code->php("\$_scope->\$key = new Smarty_Variable (\$value);")->newline();
        $code->outdent()->php("}")->newline();
        $code->mergeCode($compiler->template_code);
        $code->outdent()->php("}")->newline();

        $compiler->template_functions_code[$_name] = $code;

        // reset flag that we are compiling a template function
        $compiler->compiles_template_function = false;
        // restore old compiler status
        $compiler->template_code = $saved_data[1];

        $compiler->has_nocache_code = $compiler->has_nocache_code | $saved_data[2];
        $compiler->has_code = false;

        return true;
    }

}
