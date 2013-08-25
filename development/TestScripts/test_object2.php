<?php
/**
 * Test script for registered objects
 * @author Uwe Tews
 * @package SmartyTestScripts
 */

require '../../distribution/libs/Smarty.class.php';

$smarty = new Smarty;

$smarty->force_compile = true;
$smarty->caching = false;
$smarty->cache_lifetime = 60;
$smarty->use_sub_dirs = false;

class TestClass
{

    public function hello($params)
    {
        return count($params);
    }

}

function test($o)
{
    return $o;
}

$object = new TestClass;

echo 'func ' . test($object)->hello('ja');

$smarty->register->templateObject('test', $object, null, true, null);
$smarty->assign('o', $object);
$smarty->display('test_object2.tpl');
