<?php
/**
 * Test script for resource plugins
 * @author Uwe Tews
 * @package SmartyTestScripts
 */

require '../../distribution/libs/Smarty.class.php';

$smarty = new Smarty;

//$smarty->debugging = true;
//$smarty->force_compile = true;
//$smarty->caching = 1;
$smarty->cache_lifetime = 10;
$smarty->plugins_dir[] = './plugins';

//$smarty->display('message:registered.tpl');
//$smarty->display('string:{include file=\'db:test\'}');
//$smarty->display('string:{include file=\'registered.tpl\'}');
$smarty->display('tr:trtest');

//var_dump($smarty->resource_objects);
