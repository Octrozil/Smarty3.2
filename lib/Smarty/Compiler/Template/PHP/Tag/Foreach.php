<?php

/**
 * Smarty Internal Plugin Compile Foreach
 *
 * Compiles the {foreach} {foreachelse} {/foreach} tags
 *
 *
 * @package Compiler
 * @author Uwe Tews
 */

/**
 * Smarty Internal Plugin Compile Foreach Class
 *
 *
 * @package Compiler
 */
class Smarty_Compiler_Template_Php_Tag_Foreach extends Smarty_Compiler_Template_Php_Tag
{

    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see $tpl_obj
     */
    public $required_attributes = array('from', 'item');

    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see $tpl_obj
     */
    public $optional_attributes = array('name', 'key');

    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see $tpl_obj
     */
    public $shorttag_order = array('from', 'item', 'key', 'name');

    /**
     * Compiles code for the {foreach} tag
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

        $from = $_attr['from'];
        $item = trim($_attr['item'], '\'"');
        if ($item == substr($from, 24, -7)) {
            $compiler->error("'item' variable '\${$item}' may not be the same variable as at 'from'", $compiler->lex->taglineno);
        }

        if (isset($_attr['key'])) {
            $key = trim($_attr['key'], '\'"');
        } else {
            $key = null;
        }

        $this->openTag($compiler, 'foreach', array('foreach', $compiler->nocache, $item, $key));
        // maybe nocache because of nocache variables
        $compiler->nocache = $compiler->nocache | $compiler->tag_nocache;

        $this->iniTagCode($compiler);

        if (isset($_attr['name'])) {
            $name = $_attr['name'];
            $has_name = true;
            $SmartyVarName = '$smarty.foreach.' . trim($name, '\'"') . '.';
        } else {
            $name = null;
            $has_name = false;
        }
        $ItemVarName = '$' . $item . '@';
        // evaluates which Smarty variables and properties have to be computed
        if ($has_name) {
            $usesSmartyFirst = strpos($compiler->lex->data, $SmartyVarName . 'first') !== false;
            $usesSmartyLast = strpos($compiler->lex->data, $SmartyVarName . 'last') !== false;
            $usesSmartyIndex = strpos($compiler->lex->data, $SmartyVarName . 'index') !== false;
            $usesSmartyIteration = strpos($compiler->lex->data, $SmartyVarName . 'iteration') !== false;
            $usesSmartyShow = strpos($compiler->lex->data, $SmartyVarName . 'show') !== false;
            $usesSmartyTotal = strpos($compiler->lex->data, $SmartyVarName . 'total') !== false;
        } else {
            $usesSmartyFirst = false;
            $usesSmartyLast = false;
            $usesSmartyTotal = false;
            $usesSmartyShow = false;
        }

        $usesPropKey = strpos($compiler->lex->data, $ItemVarName . 'key') !== false;
        $usesPropFirst = $usesSmartyFirst || strpos($compiler->lex->data, $ItemVarName . 'first') !== false;
        $usesPropLast = $usesSmartyLast || strpos($compiler->lex->data, $ItemVarName . 'last') !== false;
        $usesPropIndex = $usesPropFirst || strpos($compiler->lex->data, $ItemVarName . 'index') !== false;
        $usesPropIteration = $usesPropLast || strpos($compiler->lex->data, $ItemVarName . 'iteration') !== false;
        $usesPropShow = strpos($compiler->lex->data, $ItemVarName . 'show') !== false;
        $usesPropTotal = $usesSmartyTotal || $usesSmartyShow || $usesPropShow || $usesPropLast || strpos($compiler->lex->data, $ItemVarName . 'total') !== false;
        // generate output code
        $this->php("\$this->_assignInScope('$item', new Smarty_Variable);")->newline();
//        $this->php("\$_scope->_tpl_vars->$item = new Smarty_Variable;")->newline();
        $this->php("\$_scope->_tpl_vars->{$item}->_loop = false;")->newline();
        if ($key != null) {
            $this->php("\$this->_assignInScope('$key', new Smarty_Variable);")->newline();
//            $this->php("\$_scope->_tpl_vars->$key = new Smarty_Variable;")->newline();
        }
        $this->php("\$_from = $from;")->newline();
        $this->php("if (!is_array(\$_from) && !is_object(\$_from)) {")->newline()->indent()->php("settype(\$_from, 'array');")->newline()->outdent()->php("}")->newline();
        if ($usesPropTotal) {
            $this->php("\$_scope->_tpl_vars->{$item}->total = \$this->_count(\$_from);")->newline();
        }
        if ($usesPropIteration) {
            $this->php("\$_scope->_tpl_vars->{$item}->iteration = 0;")->newline();
        }
        if ($usesPropIndex) {
            $this->php("\$_scope->_tpl_vars->{$item}->index = -1;")->newline();
        }
        if ($usesPropShow) {
            $this->php("\$_scope->_tpl_vars->{$item}->show = (\$_scope->_tpl_vars->{$item}->total > 0);")->newline();
        }
        if ($has_name) {
            $varname = 'smarty_foreach_' . trim($name, '\'"');
            $this->php("\$this->_assignInScope('$varname', new Smarty_Variable);")->newline();
//            $this->php("\$_scope->_tpl_vars->{$varname} = new Smarty_Variable;")->newline();
            if ($usesSmartyTotal) {
                $this->php("\$_scope->_tpl_vars->{$varname}->value['total'] = \$_scope->_tpl_vars->{$item}->total;")->newline();
            }
            if ($usesSmartyIteration) {
                $this->php("\$_scope->_tpl_vars->{$varname}->value['iteration'] = 0;")->newline();
            }
            if ($usesSmartyIndex) {
                $this->php("\$_scope->_tpl_vars->{$varname}->value['index'] = -1;")->newline();
            }
            if ($usesSmartyShow) {
                $this->php("\$_scope->_tpl_vars->{$varname}->value['show']=(\$_scope->_tpl_vars->{$item}->total > 0);")->newline();
            }
        }
        $keyterm = '';
        if ($key != null) {
            $keyterm = "\$_scope->_tpl_vars->{$key}->value =>";;
        } else if ($usesPropKey) {
            $keyterm = "\$_scope->_tpl_vars->{$item}->key =>";
        }
        $this->php("foreach (\$_from as " . $keyterm . " \$_scope->_tpl_vars->{$item}->value) {")->indent()->newline();
        $this->php("\$_scope->_tpl_vars->{$item}->_loop = true;")->newline();
        if ($key != null && $usesPropKey) {
            $this->php("\$_scope->_tpl_vars->{$item}->key = \$_scope->_tpl_vars->{$key}->value;")->newline();
        }
        if ($usesPropIteration) {
            $this->php("\$_scope->_tpl_vars->{$item}->iteration++;")->newline();
        }
        if ($usesPropIndex) {
            $this->php("\$_scope->_tpl_vars->{$item}->index++;")->newline();
        }
        if ($usesPropFirst) {
            $this->php("\$_scope->_tpl_vars->{$item}->first = \$_scope->_tpl_vars->{$item}->index === 0;")->newline();
        }
        if ($usesPropLast) {
            $this->php("\$_scope->_tpl_vars->{$item}->last = \$_scope->_tpl_vars->{$item}->iteration === \$_scope->_tpl_vars->{$item}->total;")->newline();
        }
        if ($has_name) {
            if ($usesSmartyFirst) {
                $this->php("\$_scope->_tpl_vars->{$varname}->value['first'] = \$_scope->_tpl_vars->{$item}->first;")->newline();
            }
            if ($usesSmartyIteration) {
                $this->php("\$_scope->_tpl_vars->{$varname}->value['iteration']++;")->newline();
            }
            if ($usesSmartyIndex) {
                $this->php("\$_scope->_tpl_vars->{$varname}->value['index']++;")->newline();
            }
            if ($usesSmartyLast) {
                $this->php("\$_scope->_tpl_vars->{$varname}->value['last'] = \$_scope->_tpl_vars->{$item}->last;")->newline();
            }
        }

        return $this->returnTagCode($compiler);
    }

}

/**
 * Smarty Internal Plugin Compile Foreachelse Class
 *
 *
 * @package Compiler
 */
class Smarty_Compiler_Template_Php_Tag_Foreachelse extends Smarty_Compiler_Template_Php_Tag
{

    /**
     * Compiles code for the {foreachelse} tag
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

        list($openTag, $nocache, $item, $key) = $this->closeTag($compiler, array('foreach'));
        $this->openTag($compiler, 'foreachelse', array('foreachelse', $nocache, $item, $key));

        $this->iniTagCode($compiler);

        $this->outdent()->php("}")->newline();
        $this->php("if (!\$_scope->_tpl_vars->{$item}->_loop) {")->newline()->indent();

        return $this->returnTagCode($compiler);
    }

}

/**
 * Smarty Internal Plugin Compile Foreachclose Class
 *
 *
 * @package Compiler
 */
class Smarty_Compiler_Template_Php_Tag_Foreachclose extends Smarty_Compiler_Template_Php_Tag
{

    /**
     * Compiles code for the {/foreach} tag
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
        // must endblock be nocache?
        if ($compiler->nocache) {
            $compiler->tag_nocache = true;
        }

        list($openTag, $compiler->nocache, $item, $key) = $this->closeTag($compiler, array('foreach', 'foreachelse'));

        $this->iniTagCode($compiler);

        $this->outdent()->php("}")->newline();

        return $this->returnTagCode($compiler);
    }

}
