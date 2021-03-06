<?php

/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsModifierCompiler
 */

/**
 * Smarty wordwrap modifier plugin
 * Type:     modifier<br>
 * Name:     wordwrap<br>
 * Purpose:  wrap a string of text at a given length
 *
 * @link   http://www.smarty.net/docs/en/language.modifier.wordwrap.tpl wordwrap (Smarty online manual)
 * @author Uwe Tews
 *
 * @param  Smarty_Compiler_Template_Php_Compiler $compiler compiler object
 * @param string                                 $input    input string
 * @param int                                    $columns  number of columns before wrap
 * @param string                                 $wrap     string to use to wrap
 * @param bool|string                            $cut      if true wrap exact at column count
 *
 * @return string with compiled code
 */
// NOTE: The parser does pass all parameter as strings which could be directly inserted into the compiled code string
function smarty_modifiercompiler_wordwrap(Smarty_Compiler_Template_Php_Compiler $compiler, $input, $columns = 80, $wrap = '"\n"', $cut = 'false')
{
    $function = 'wordwrap';
    if (Smarty::$_MBSTRING) {
        if ($compiler->tag_nocache | $compiler->nocache) {
            $compiler->required_plugins['nocache']['wordwrap']['modifier']['file'] = Smarty::$_SMARTY_PLUGINS_DIR . 'shared.mb_wordwrap.php';
            $compiler->required_plugins['nocache']['wordwrap']['modifier']['function'] = 'smarty_mb_wordwrap';
        } else {
            $compiler->required_plugins['compiled']['wordwrap']['modifier']['file'] = Smarty::$_SMARTY_PLUGINS_DIR . 'shared.mb_wordwrap.php';
            $compiler->required_plugins['compiled']['wordwrap']['modifier']['function'] = 'smarty_mb_wordwrap';
        }
        $function = 'smarty_mb_wordwrap';
    }

    return $function . "({$input}, {$columns}, {$wrap}, {$cut})";
}
