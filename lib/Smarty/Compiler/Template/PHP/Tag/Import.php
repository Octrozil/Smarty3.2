<?php

/**
 * Smarty Internal Plugin Compile Import
 *
 * Compiles the {import} tag
 *
 *
 * @package Compiler
 * @author Uwe Tews
 */

/**
 * Smarty Internal Plugin Compile Import Class
 *
 *
 * @package Compiler
 */
class Smarty_Compiler_Template_Php_Tag_Import extends Smarty_Compiler_Template_Php_Tag
{

    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see $tpl_obj
     */
    public $required_attributes = array('file');

    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see $tpl_obj
     */
    public $shorttag_order = array('file');

    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see $tpl_obj
     */
    public $option_flags = array();

    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see $tpl_obj
     */
    public $optional_attributes = array();

    /**
     * Compiles code for the {import} tag
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

        $include_file = $_attr['file'];
        if (!(substr_count($include_file, "'") == 2 || substr_count($include_file, '"') == 2)) {
            $compiler->error('illegal variable template name', $compiler->lex->taglineno);
        }
        $_scope = $compiler->context->scope;
        eval("\$tpl_name = $include_file;");
        $source = Smarty_Context::getContext($compiler->context->smarty, $tpl_name);
        $comp = Smarty_Compiler::load($compiler->context->smarty, $source, $compiler->context->caching);
        $comp->nocache = $compiler->nocache;
        // set up parameter
        $comp->suppressTemplatePropertyHeader = true;
        $comp->suppressPostFilter = true;
        $comp->write_compiled_code = false;
        $comp->template_code->indentation = $compiler->template_code->indentation;
        $comp->isInheritance = $compiler->isInheritance;
        $comp->isInheritanceChild = $compiler->isInheritanceChild;
        // compile imported template
        $comp->template_code->php("/*  Imported template \"{$tpl_name}\" */")->newline();
        $comp->compileTemplate();
        $comp->template_code->php("/*  End of imported template \"{$tpl_name}\" */")->newline();
        // merge compiled code for {function} tags
        if (!empty($comp->template_functions)) {
            $compiler->template_functions = array_merge($compiler->template_functions, $comp->template_functions);
            $compiler->template_functions_code = array_merge($compiler->template_functions_code, $comp->template_functions_code);
        }
        // merge compiled code for {block} tags
        if (!empty($comp->inheritance_blocks)) {
            $compiler->inheritance_blocks = array_merge($compiler->inheritance_blocks, $comp->inheritance_blocks);
            $compiler->inheritance_blocks_code = array_merge($compiler->inheritance_blocks_code, $comp->inheritance_blocks_code);
        }
        $compiler->required_plugins['compiled'] = array_merge($compiler->required_plugins['compiled'], $comp->required_plugins['compiled']);
        $compiler->required_plugins['nocache'] = array_merge($compiler->required_plugins['nocache'], $comp->required_plugins['nocache']);
        // merge filedependency
        $compiler->file_dependency[$tpl->context->uid] = array($tpl->context->filepath, $tpl->context->timestamp, $tpl->context->type);
        $compiler->file_dependency = array_merge($compiler->file_dependency, $comp->file_dependency);
        $compiler->has_nocache_code = $compiler->has_nocache_code | $comp->has_nocache_code;

        $save = $compiler->nocache_nolog;
        $compiler->nocache_nolog = $save;
        // output compiled code

        $compiler->suppressNocacheProcessing = true;
        $this->iniTagCode($compiler);
        $this->buffer .= $comp->template_code->buffer;
        // release compiler object to free memory
        unset($comp);

        return $this->returnTagCode($compiler);
    }

}
