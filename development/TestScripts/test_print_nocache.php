<?php
/**
 * Test script for nocache variables
 *
 * @author Uwe Tews
 * @package SmartyTestScripts
 */

require '../../distribution/libs/Smarty.class.php';

$smarty = new Smarty;

$smarty->force_compile = false;
$smarty->caching = true;
$smarty->cache_lifetime = 10;
$smarty->debugging = true;

$smarty->assign('foo', 2);
$smarty->assign('bar', 'B');

$smarty->display('test_print_nocache.tpl');
