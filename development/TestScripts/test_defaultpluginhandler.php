<?php
/**
 * Test script for the Smarty compiler
 *
 * It displays a form in which a template source code can be entered.
 * The template source will be compiled, rendered and the result is displayed.
 * The compiled code is displayed as well
 *
 * @author Uwe Tews
 * @package SmartyTestScripts
 */

require '../../distribution/libs/Smarty.class.php';

function handler($tag, $type, $template, &$callback, &$script)
{
    if ($type == Smarty::PLUGIN_FUNCTION) {
        $script = '../PHPunit/scripts/script_function_tag.php';
        $callback = 'default_function_tag';

        return true;
    }

    return false;
}

$smarty = new Smarty;
$smarty->default_plugin_handler_func = 'handler';
$smarty->error_reporting = E_ALL + E_STRICT;
$smarty->addPluginsDir('./plugins');

//$smarty->force_compile = true;
$smarty->debugging = true;
//$smarty->use_sub_dirs = true;
//$smarty->caching = 1;
//$smarty->enableCaching();
$smarty->cache_lifetime = 10;
//$smarty->merge_compiled_includes = true;
//$smarty->error_unassigned = true;
//$smarty->enableSecurity();

$smarty->display('test_default_plugin.tpl');
