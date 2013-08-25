<?php
/**
 * Test script for methode chaining
 * @author Uwe Tews
 * @package SmartyTestScripts
 */

require '../../distribution/libs/Smarty.class.php';

$smarty = new Smarty;

$smarty->force_compile = true;
$smarty->caching = false;
$smarty->cache_lifetime = 60;
$smarty->use_sub_dirs = false;

class Test
{
    public $hello = 'hello_world';
}

$object = new Test;
$smarty->assign('object', $object);

$smarty->display('string:{$object->hello}');
