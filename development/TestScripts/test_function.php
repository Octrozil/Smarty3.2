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

$smarty = new Smarty;
$smarty->error_reporting = E_ALL + E_STRICT;
$smarty->addPluginsDir('./plugins');
$smarty->error_reporting = E_ALL + E_STRICT;

$smarty->force_compile = true;
//$smarty->debugging = true;
//$smarty->use_sub_dirs = true;
//$smarty->enableCaching();
$smarty->cache_lifetime = 100000;
//$smarty->merge_compiled_includes = true;
//$smarty->error_unassigned = true;
//$smarty->enableSecurity();

//$smarty->load_filter('output', 'move_to_head');

//$smarty->display('child.tpl');
//$smarty->caching_type = 'mysql';
//$smarty->clear_all_cache(time()-100);
//echo $smarty->clear_cache(null,'uwe');
//$smarty->compile_id = 'bar';
//$smarty->cache_id = 'uwe|tews';
//$smarty->caching = 1;

$smarty->assign('foo', false);
//$smarty->display('test_template_function_nocache_call.tpl');
$smarty->templateExists('test_function2.tpl');
//$smarty->clearCompiledTemplate('test_function2.tpl');
$smarty->clearCache('test_function2.tpl');
$smarty->display('test_function2.tpl');
//$smarty->display('child.tpl');
