<?php
/**
 * Test script for extend resource
 * @author Uwe Tews
 * @package SmartyTestScripts
 */

require '../../distribution/libs/Smarty.class.php';

$smarty = new Smarty;
$smarty->debugging = true;
$smarty->force_compile = true;
//$smarty->caching = true;
//$smarty->use_sub_dirs = true;
$smarty->cache_lifetime = 1000;
$smarty->left_delimiter = '{';
$smarty->right_delimiter = '}';

//$smarty->display('extends:parent_nested.tpl|child_nested.tpl|grandchild_nested.tpl');
$smarty->display('grandchild_nested.tpl');
