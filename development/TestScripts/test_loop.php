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
error_reporting(E_ALL + E_STRICT);
$smarty->plugins_dir[] = './plugins';

$data = new Smarty_Data;

//$smarty->_parserdebug= true;
$smarty->merge_compiled_includes = true;
//$smarty->force_compile = true;
//$smarty->debugging = true;
//$smarty->use_sub_dirs = true;
$smarty->cache_lifetime = 20;
//$smarty->caching = 1;
//$smarty->cache_modified_check = true;
//$smarty->enableCaching();
//$smarty->merge_compiled_includes = true;
//$smarty->error_unassigned = true;
//$smarty->enableSecurity();
//$smarty->loadFilter('output', 'trimwhitespace');
//$smarty->loadFilter("variable", "htmlspecialchars");
//$smarty->load_filter('output', 'move_to_head');

//$smarty->display('child.tpl');
//$smarty->clear_all_cache(time()-100);
//echo $smarty->clear_cache(null,'uwe');
//$smarty->compile_id = 'bar';
//$smarty->cache_id = 'uwe|tews';

$smarty->display('test_loop.tpl');
