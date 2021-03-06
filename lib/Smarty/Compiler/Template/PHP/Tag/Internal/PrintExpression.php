<?php

/**
 * Smarty Internal Plugin Compile Print Expression
 * Compiles any tag which will output an expression or variable
 *
 * @package Compiler
 * @author  Uwe Tews
 */

/**
 * Smarty Internal Plugin Compile Print Expression Class
 *
 * @package Compiler
 */
class Smarty_Compiler_Template_Php_Tag_Internal_PrintExpression extends Smarty_Compiler_Template_Php_Tag
{

    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see $tpl_obj
     */
    public $optional_attributes = array('assign');

    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see $tpl_obj
     */
    public $option_flags = array('nocache', 'nofilter');

    /**
     * Compiles code for gererting output from any expression
     *
     * @param  array  $args      array with attributes from parser
     * @param  object $compiler  compiler object
     * @param  array  $parameter array with compilation parameter
     *
     * @throws Smarty_Exception
     * @return string           compiled code
     */
    public function compile($args, $compiler, $parameter)
    {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);
        // nocache option
        if ($_attr['nocache'] === true) {
            $compiler->tag_nocache = true;
        }
        // filter handling
        if ($_attr['nofilter'] === true) {
            $_filter = 'false';
        } else {
            $_filter = 'true';
        }
        $this->iniTagCode($compiler);
        if (isset($_attr['assign'])) {
            // assign output to variable
            $this->php("\$this->assign({$_attr['assign']},{$parameter['value']});")->newline();
        } else {
            $this->php("echo ");
            // display value
            $output = $parameter['value'];
            // tag modifier
            if (! empty($parameter['modifier_list'])) {
                $output = $compiler->compileTag('Internal_Modifier', array(), array('modifier_list' => $parameter['modifier_list'], 'value' => $output));
            }
            if (! $_attr['nofilter']) {
                // default modifier
                if (! empty($compiler->context->smarty->default_modifiers)) {
                    if (empty($compiler->default_modifier_list)) {
                        $modifierlist = array();
                        foreach ($compiler->context->smarty->default_modifiers as $key => $single_default_modifier) {
                            preg_match_all('/(\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'|"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"|:|[^:]+)/', $single_default_modifier, $mod_array);
                            for ($i = 0, $count = count($mod_array[0]); $i < $count; $i ++) {
                                if ($mod_array[0][$i] != ':') {
                                    $modifierlist[$key][] = $mod_array[0][$i];
                                }
                            }
                        }
                        $compiler->default_modifier_list = $modifierlist;
                    }
                    $output = $compiler->compileTag('Internal_Modifier', array(), array('modifier_list' => $compiler->default_modifier_list, 'value' => $output));
                }
                // autoescape html
                if ($compiler->context->smarty->escape_html) {
                    $output = "htmlspecialchars({$output}, ENT_QUOTES, '" . addslashes(Smarty::$_CHARSET) . "')";
                }
                // loop over registerd filters
                if (! empty($compiler->context->smarty->_registered['filter'][Smarty::FILTER_VARIABLE])) {
                    foreach ($compiler->context->smarty->_registered['filter'][Smarty::FILTER_VARIABLE] as $key => $function) {
                        if ($function instanceof Closure) {
                            $output = "\$this->smarty->_registered['filter'][Smarty::FILTER_VARIABLE]['{$key}']({$output},\$this->smarty)";
                        } elseif (! is_array($function)) {
                            $output = "{$function}({$output},\$this->smarty)";
                        } elseif (is_object($function[0])) {
                            $output = "\$this->smarty->_registered['filter'][Smarty::FILTER_VARIABLE]['{$key}'][0]->{$function[1]}({$output},\$this->smarty)";
                        } else {
                            $output = "{$function[0]}::{$function[1]}({$output},\$this->smarty)";
                        }
                    }
                }
                // auto loaded filters
                if (isset($compiler->context->smarty->autoload_filters[Smarty::FILTER_VARIABLE])) {
                    foreach ((array)$compiler->context->smarty->autoload_filters[Smarty::FILTER_VARIABLE] as $name) {
                        $result = $this->compile_output_filter($compiler, $name, $output);
                        if ($result !== false) {
                            $output = $result;
                        } else {
                            // not found, throw exception
                            throw new Smarty_Exception("Unable to load filter '{$name}'");
                        }
                    }
                }
                if (isset($compiler->context->smarty->variable_filters)) {
                    foreach ($compiler->context->smarty->variable_filters as $filter) {
                        if (count($filter) == 1 && ($result = $this->compile_output_filter($compiler, $filter[0], $output)) !== false) {
                            $output = $result;
                        } else {
                            $output = $compiler->compileTag('Internal_Modifier', array(), array('modifier_list' => array($filter), 'value' => $output));
                        }
                    }
                }
            }

            $compiler->has_output = true;
            $this->raw(" {$output};")->newline();
        }

        return $this->returnTagCode($compiler);
    }

    /**
     * @param  object $compiler compiler object
     * @param  string $name     name of variable filter
     * @param  string $output   embedded output
     *
     * @return string
     */
    private function compile_output_filter($compiler, $name, $output)
    {
        $plugin_name = "smarty_variablefilter_{$name}";
        $path = $compiler->context->smarty->_loadPlugin($plugin_name, false);
        if ($path) {
            if ($compiler->context->caching) {
                $compiler->required_plugins['nocache'][$name][Smarty::FILTER_VARIABLE]['file'] = $path;
                $compiler->required_plugins['nocache'][$name][Smarty::FILTER_VARIABLE]['function'] = $plugin_name;
            } else {
                $compiler->required_plugins['compiled'][$name][Smarty::FILTER_VARIABLE]['file'] = $path;
                $compiler->required_plugins['compiled'][$name][Smarty::FILTER_VARIABLE]['function'] = $plugin_name;
            }
        } else {
            // not found
            return false;
        }

        return "{$plugin_name}({$output},\$this->smarty)";
    }

}
