<?php
/**
 * Test script for nocache sections
 *
 * @author Uwe Tews
 * @package SmartyTestScripts
 */

require '../../distribution/libs/Smarty.class.php';

$smarty = new Smarty;

//$smarty->merge_compiled_includes = true;
//$smarty->force_compile = true;
//$smarty->compile_check = false;
$smarty->addPluginsDir('./plugins');
$smarty->loadFilter('output', 'testuwe');
$smarty->cache_lifetime = 10;

//$smarty->caching = true;
//$smarty->debugging = true;
$smarty->assign('foo', 'include3');

//$tpl = $smarty->createTemplate('include.tpl');
echo '<br>' . memory_get_peak_usage(true) . '<br>';
$smarty->display('include.tpl');
echo '<br>' . memory_get_peak_usage(true) . '<br>';
