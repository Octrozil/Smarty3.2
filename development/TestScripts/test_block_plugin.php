<?php
/**
 * Test script for the function plugin tag
 *
 * @author Uwe Tews
 * @package SmartyTestScripts
 */

require '../../distribution/libs/Smarty.class.php';

$smarty = new Smarty;

$smarty->force_compile = false;
$smarty->caching = true;
$smarty->cache_lifetime = 10;

$object = new RegObject;
$smarty->register->object('objecttest', $object, 'myhello', false, 'myblock');
$smarty->assign('objecttest', $object);
$smarty->display('test_block_plugin.tpl');

Class RegObject
{
    public $test = 'test string';

    public function myhello()
    {
        return 'hello world';
    }

    public function myblock($params, $content, &$smarty_tpl, &$repeat)
    {
        if (!$repeat) {
            $output = str_replace('hello world', 'block test', $content);

            return $output;
        }
    }
}
