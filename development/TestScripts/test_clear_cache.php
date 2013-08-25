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
error_reporting(E_ALL + E_STRICT);
$smarty->error_reporting = E_ALL + E_STRICT + E_NOTICE;
$smarty->plugins_dir[] = './plugins';

$smarty->caching = true;
//      $smarty->compile_id = 'blar2';
$smarty->cache_lifetime = 1000;
$smarty->cache->clearAll();
//        $smarty->use_sub_dirs = true;
$tpl = $smarty->createTemplate('helloworld.tpl', null, 'blar2');
$smarty->fetch($tpl);
$tpl1 = $smarty->createTemplate('helloworld.tpl', 'foo|bar');
$smarty->fetch($tpl1);
$tpl11 = $smarty->createTemplate('helloworld.tpl', 'foo', 'blar2');
$smarty->fetch($tpl11);
$tpl2 = $smarty->createTemplate('helloworld.tpl', 'foo|bar', 'blar2');
$smarty->fetch($tpl2);
$tpl3 = $smarty->createTemplate('helloworld2.tpl');
$smarty->fetch($tpl3);
$tpl4 = $smarty->createTemplate('test/helloworld3.tpl');
$smarty->fetch($tpl4);
//      $smarty->caching = false;
echo $smarty->cache->clear('helloworld.tpl', null, 'blar2') . '  ';
echo $smarty->cache->clear(null, null, 'blar2');
