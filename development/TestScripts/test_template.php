<?php
/**
 * Test script for the {debug} tag
 * @author Uwe Tews
 * @package SmartyTestScripts
 */

require '../../distribution/libs/Smarty.class.php';

$smarty = new Smarty;
$smarty->force_compile = false;
$smarty->debugging = true;
//$smarty->caching = true;
$smarty->cache_lifetime = 30;

$smarty->display('test_template.tpl');
