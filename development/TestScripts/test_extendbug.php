<?php
/**
 * Test script for extend resource
 * @author Uwe Tews
 * @package SmartyTestScripts
 */

require '../../distribution/libs/Smarty.class.php';

$smarty = new Smarty;
$smarty->debugging = true;
//$smarty->force_compile = true;
$smarty->caching = true;
//$smarty->use_sub_dirs = true;
$smarty->cache_lifetime = 1000;

$smarty->assign('test', time());
$smarty->assign('content', 'this is the content');

$theme = 'default.tpl';
$view = 'create.tpl';

//$smarty->display('extends:' . $theme . '|' . $view, null, null);
$smarty->template_dir = array('.' . DS . 'templates' . DS . 'mypro' . DS . 'mycod' . DS, '.' . DS . 'templates' . DS . 'mypro' . DS, '.' . DS . 'templates' . DS);
$smarty->display('extends:defaultbody.tpl|probody.tpl|codbody.tpl');
