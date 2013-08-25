<?php
/**
 * Test script for extend resource
 * @author Uwe Tews
 * @package SmartyTestScripts
 */

require '../../distribution/libs/Smarty.class.php';
function prefilter($content)
{
    return str_replace('%%%uwe%%', '%tews%', $content);
}

$smarty = new Smarty;
//$smarty->debugging = true;
//$smarty->force_compile = true;
//$smarty->template_dir[] = '.\templates\bug';
$smarty->caching = true;
//$smarty->use_sub_dirs = true;
//$smarty->_parserdebug=true;
$smarty->cache_lifetime = 1000;
$smarty->left_delimiter = '{';
$smarty->right_delimiter = '}';
//$smarty->registerFilter('pre','prefilter');
$smarty->assign('foo', true);

//if (!$smarty->isCached('extends:test_block_base.tpl|test_block_section.tpl|c:/wamp/www/smarty3.1.0/development/TestScripts/templates/test_block.tpl','me')) {
//    echo 'refresh';
//}
//$smarty->display('extends:test_block_base.tpl|test_block_section.tpl|c:/wamp/www/smarty3.1.0/development/TestScripts/templates/test_block.tpl','me');
//$smarty->display('extends:test_block_parent.tpl|test_block_child_e.tpl|test_block_grandchild_e.tpl');
$smarty->display('test_block_grandchild.tpl');
