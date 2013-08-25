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
//$smarty->force_compile = true;
$smarty->debugging = true;
//$smarty->use_sub_dirs = true;
$smarty->cache_lifetime = 10;
$smarty->caching = 1;
$smarty->php_handling = SMARTY_PHP_QUOTE;

$smarty->display('test_xml.tpl');
