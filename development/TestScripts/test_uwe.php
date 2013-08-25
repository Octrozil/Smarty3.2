<?php
/**
 * Test script for nocache sections
 * @author Uwe Tews
 * @package SmartyTestScripts
 */

require '../../distribution/libs/Smarty.class.php';

$smarty = new Smarty;

$smarty->force_compile = true;
$smarty->caching = 0;
$smarty->cache_lifetime = 20;

$smarty->display('test_uwe.tpl');
