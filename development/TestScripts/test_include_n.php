<?php
/**
 * Test script for nocache variables
 * @author Uwe Tews
 * @package SmartyTestScripts
 */

require '../../distribution/libs/Smarty.class.php';

$smarty = new Smarty;

$smarty->force_compile = true;
//$smarty->caching = true;
$smarty->cache_lifetime = 10;
$smarty->debugging = true;

$smarty->display('test_include_n0.tpl');
