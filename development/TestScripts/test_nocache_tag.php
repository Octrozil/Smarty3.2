<?php
/**
 * Test script for nocache sections
 *
 * @author Uwe Tews
 * @package SmartyTestScripts
 */

require '../../distribution/libs/Smarty.class.php';

$smarty = new Smarty;

$smarty->force_compile = false;
$smarty->caching = true;
$smarty->cache_lifetime = 10;

$smarty->assign('foo', 0);
$smarty->assign('bar', 'A');
$smarty->display('test_nocache_tag.tpl');

$smarty->assign('foo', 2);
$smarty->assign('bar', 'B');
$smarty->display('test_nocache_tag.tpl');
