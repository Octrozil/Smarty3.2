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
// set_time_limit(1000);
ini_set('pcre.backtrack_limit', -1);

$smarty = new Smarty;
$smarty->setErrorReporting(E_ALL + E_STRICT);
error_reporting(E_ALL + E_STRICT);
$smarty->plugins_dir[] = './plugins';
$smarty->setCaching(true);
$smarty->debugging = true;
$smarty->setCacheLifetime(15);
//$smarty->force_compile = true;

$start = microtime(true);
$smarty->display('test_block_include_root.tpl');
echo '<br>' . (microtime(true) - $start) . '<br>';
echo '<br>' . memory_get_peak_usage();
