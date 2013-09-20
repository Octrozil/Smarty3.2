<?php

/**
 * Smarty Internal Plugin Compile Assign
 *
 * Compiles the {assign} tag
 *
 *
 * @package Smarty\Compiler\PHP\Tag
 * @author Uwe Tews
 */

/**
 * Smarty Internal Plugin Compile Assign Class
 *
 *
 * @package Smarty\Compiler\PHP\Tag
 */
class Smarty_Compiler_Template_Php_Tag_Assign extends Smarty_Compiler_Template_Php_Tag
{

    /**
     * Compiles code for the {assign} tag
     *
     * @param  array $args      array with attributes from parser
     * @param  object $compiler  compiler object
     * @param  array $parameter array with compilation parameter
     * @return string compiled code
     */
    public function compile($args, $compiler, $parameter)
    {
        // the following must be assigned at runtime because it will be overwritten in Smarty_Compiler_Template_Php_Tag_Append
        $this->required_attributes = array('var', 'value');
        $this->shorttag_order = array('var', 'value');
        $this->optional_attributes = array('scope');
        $this->option_flags = array('nocache', 'cachevalue');

        $_nocache = 'false';
        $scope_type = Smarty::SCOPE_LOCAL;
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);
        $var = trim($_attr['var'], '\'"');
        // nocache ?
        if ($compiler->tag_nocache || $compiler->nocache) {
            $_nocache = 'true';
            // create nocache var to make it know for further compiling
            if (isset($compiler->tpl_obj->_tpl_vars->$var)) {
                $compiler->tpl_obj->_tpl_vars->$var->nocache = true;
            } else {
                $compiler->tpl_obj->_tpl_vars->$var = new Smarty_Variable(null, true);
            }
        }
        // scope setup
        if (isset($_attr['scope'])) {
            $_attr['scope'] = trim($_attr['scope'], "'\"");
            if ($_attr['scope'] == 'parent') {
                $scope_type = Smarty::SCOPE_PARENT;
            } elseif ($_attr['scope'] == 'root') {
                $scope_type = Smarty::SCOPE_ROOT;
            } elseif ($_attr['scope'] == 'global') {
                $scope_type = Smarty::SCOPE_GLOBAL;
            } else {
                $compiler->error('illegal value for "scope" attribute', $compiler->lex->taglineno);
            }
        }
        // compiled output
        $this->iniTagCode($compiler);

        if ($scope_type == Smarty::SCOPE_GLOBAL) {
            $scopeString = 'Smarty::$_global_tpl_vars';
        } else {
            $scopeString = '$_scope';
        }

        if (isset($parameter['smarty_internal_index'])) {
            $this->php("\$this->_createLocalArrayVariable('{$var}', {$_nocache}, {$scope_type});")->newline();
            $this->php("{$scopeString}->{$var}->value{$parameter['smarty_internal_index']} = {$_attr['value']};")->newline();
        } else {
            $this->php("\$this->_assignInScope('{$var}', new Smarty_Variable($_attr[value], $_nocache), {$scope_type});")->newline();
        }

        if ($_attr['cachevalue'] === true && $compiler->caching) {
            if (isset($parameter['smarty_internal_index'])) {
                $compiler->error('cannot assign to array with "cachevalue" option', $compiler->lex->taglineno);
            } else {
                if (!$compiler->tag_nocache && !$compiler->nocache) {
                    $this->php("echo '/*%%SmartyNocache%%*/\$_scope->{$var} = new Smarty_Variable (' . \$this->_exportCacheValue({$_attr['value']}) . ');/*/%%SmartyNocache%%*/';")->newline();
                } else {
                    $compiler->error('cannot assign with "cachevalue" option inside nocache section', $compiler->lex->taglineno);
                }
            }
        }

        return $this->returnTagCode($compiler);
    }

}
