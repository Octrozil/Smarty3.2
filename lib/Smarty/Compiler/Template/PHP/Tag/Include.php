<?php

/**
 * Smarty Internal Plugin Compile Include
 *
 * Compiles the {include} tag
 *
 *
 * @package Compiler
 * @author Uwe Tews
 */

/**
 * Smarty Internal Plugin Compile Include Class
 *
 *
 * @package Compiler
 */
class Smarty_Compiler_Template_Php_Tag_Include extends Smarty_Compiler_Template_Php_Tag
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
    public $option_flags = array('nocache', 'inline', 'caching');

    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see $tpl_obj
     */
    public $optional_attributes = array('_any');

    /**
     * Compiles code for the {include} tag
     *
     * @param  array $args array with attributes from parser
     * @param  object $compiler compiler object
     * @param  array $parameter array with compilation parameter
     * @return string compiled code
     */
    public function compile($args, $compiler, $parameter)
    {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);
        // save posible attributes
        $include_file = $_attr['file'];

        if (isset($_attr['assign'])) {
            // output will be stored in a smarty variable instead of beind displayed
            $_assign = trim($_attr['assign'], "'\"");
        }

        $_parent_scope = Smarty::SCOPE_LOCAL;
        if (isset($_attr['scope'])) {
            $_attr['scope'] = trim($_attr['scope'], "'\"");
            if ($_attr['scope'] == 'parent') {
                $_parent_scope = Smarty::SCOPE_PARENT;
            } elseif ($_attr['scope'] == 'root') {
                $_parent_scope = Smarty::SCOPE_ROOT;
            } elseif ($_attr['scope'] == 'global') {
                $_parent_scope = Smarty::SCOPE_GLOBAL;
            } elseif ($_attr['scope'] == 'none') {
                $_parent_scope = Smarty::SCOPE_NONE;
            }
        }
        $_caching = Smarty::CACHING_OFF;
//        $_caching = 'null';
//        if ($compiler->nocache || $compiler->tag_nocache) {
//            $_caching = Smarty::CACHING_OFF;
//        }
        // default for included templates
        if ($compiler->context->caching && !$compiler->nocache && !$compiler->tag_nocache) {
            $_caching = Smarty::CACHING_NOCACHE_CODE;
        }
        /*
         * if the {include} tag provides individual parameter for caching
         * it will not be included into the common cache file and treated like
         * a nocache section
         */
        if (isset($_attr['cache_lifetime'])) {
            $_cache_lifetime = $_attr['cache_lifetime'];
            $compiler->nocache_nolog = true;
            $_caching = Smarty::CACHING_LIFETIME_CURRENT;
        } else {
            $_cache_lifetime = '0';
        }
        if (isset($_attr['cache_id'])) {
            $_cache_id = $_attr['cache_id'];
            $compiler->nocache_nolog = true;
            $_caching = Smarty::CACHING_LIFETIME_CURRENT;
        } else {
            $_cache_id = '$this->smarty->cache_id';
        }
        if (isset($_attr['compile_id'])) {
            $_compile_id = $_attr['compile_id'];
        } else {
            $_compile_id = '$this->smarty->compile_id';
        }
        if ($_attr['caching'] === true) {
            $compiler->nocache_nolog = true;
            $_caching = Smarty::CACHING_LIFETIME_CURRENT;
        }
        if ($_attr['nocache'] === true) {
            $compiler->tag_nocache = true;
            $_caching = Smarty::CACHING_OFF;
        }

        $code = new Smarty_Compiler_Code();
        $code->iniTagCode($compiler);

        $has_compiledtpl_obj = false;
        if (($compiler->context->smarty->merge_compiled_includes || $_attr['inline'] === true) && !$compiler->context->handler->recompiled
            && !($compiler->context->caching && ($compiler->tag_nocache || $compiler->nocache || $compiler->nocache_nolog)) && $_caching != Smarty::CACHING_LIFETIME_CURRENT
        ) {
            // check if compiled code can be merged (contains no variable part)
            if ((substr_count($include_file, '"') == 2 or substr_count($include_file, "'") == 2)
                and substr_count($include_file, '(') == 0 and substr_count($include_file, '$this->smarty->') == 0
            ) {
                $_scope = $compiler->context->scope;
                eval("\$tpl_name = $include_file;");
                // clone object
                $tpl = clone $compiler->context->smarty;
                unset($tpl->context);
                $tpl->parent = $compiler->context->smarty;
                if ($compiler->context->caching) {
                    // needs code for cached page but no cache file
                    $tpl->caching = Smarty::CACHING_NOCACHE_CODE;
                }
                // create context
                $context = $tpl->_getContext($tpl_name);
                if (!isset(Smarty_Compiler_Template_Php_Compiler::$merged_inline_content_classes[$context->uid])) {
                    // make sure whole chain gets compiled
                    $tpl->force_compile = true;
                    if (!$context->handler->uncompiled && $context->exists) {
                        $comp = Smarty_Compiler::load($context, null);
                        // get compiled code
                        $comp->suppressTemplatePropertyHeader = true;
                        $comp->write_compiled_code = false;
                        $comp->content_class = Smarty_Compiler_Template_Php_Compiler::$merged_inline_content_classes[$context->uid]['class'] = '_SmartyTemplate_' . str_replace('.', '_', uniqid('', true));
                        $comp->template_code->newline()->php("/* Inline subtemplate compiled from \"{$context->filepath}\" */")->newline();
                        $comp->compileTemplate();
                        $compiler->required_plugins['compiled'] = array_merge($compiler->required_plugins['compiled'], $comp->required_plugins['compiled']);
                        $compiler->required_plugins['nocache'] = array_merge($compiler->required_plugins['nocache'], $comp->required_plugins['nocache']);
                        $comp->required_plugins = array();
                        // merge compiled code for {function} tags
                        if (!empty($comp->template_functions)) {
                            $compiler->template_functions = array_merge($compiler->template_functions, $comp->template_functions);
                            $compiler->template_functions_code = array_merge($compiler->template_functions_code, $comp->template_functions_code);
                        }
                        // save merged template
                        Smarty_Compiler_Template_Php_Compiler::$merged_inline_content_classes[$context->uid]['code'] = $comp->_createSmartyContentClass($tpl, true);
                        // merge file dependency
                        $compiler->file_dependency[$context->uid] = array($context->filepath, $context->timestamp, $context->type);
                        $compiler->file_dependency = array_merge($compiler->file_dependency, $comp->file_dependency);
                        $compiler->has_nocache_code = $compiler->has_nocache_code | $comp->has_nocache_code;
                        $has_compiledtpl_obj = true;
                    }
                } else {
                    $has_compiledtpl_obj = true;
                }
                // release compiler object to free memory
                unset($comp, $tpl);
            }
        }
        // delete {include} standard attributes
        unset($_attr['file'], $_attr['assign'], $_attr['cache_id'], $_attr['compile_id'], $_attr['cache_lifetime'], $_attr['nocache'], $_attr['caching'], $_attr['scope'], $_attr['inline']);
        // remaining attributes must be assigned as smarty variable
        if (!empty($_attr)) {
            if ($_parent_scope == Smarty::SCOPE_LOCAL || $_parent_scope == Smarty::SCOPE_NONE) {
                // create variables
                foreach ($_attr as $key => $value) {
                    $_pairs[] = "'$key'=>$value";
                }
                $_vars = 'array(' . join(',', $_pairs) . ')';
            } else {
                $compiler->error('variable passing not allowed in parent/global scope', $compiler->lex->taglineno);
            }
        } else {
            $_vars = 'array()';
        }
        $save = $compiler->nocache_nolog;
        $compiler->nocache_nolog = $save;
        // output compiled code
        if ($has_compiledtpl_obj) {
            $_class = '\'' . Smarty_Compiler_Template_Php_Compiler::$merged_inline_content_classes[$context->uid]['class'] . '\'';
        } else {
            $_class = 'null';
        }
        // was there an assign attribute
        if (isset($_assign)) {
            $code->php("\$this->_assignInScope('{$_assign}',  new Smarty_Variable (\$this->_getSubTemplate ($include_file, $_cache_id, $_compile_id, $_caching, $_cache_lifetime, $_vars, $_parent_scope , \$_scope, $_class)));")->newline();
        } else {
            $code->php("echo \$this->_getSubTemplate ($include_file, $_cache_id, $_compile_id, $_caching, $_cache_lifetime, $_vars, $_parent_scope, \$_scope, $_class);")->newline();
        }

        return $code->returnTagCode($compiler);
    }

}
