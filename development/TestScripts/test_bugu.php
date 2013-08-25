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
function ErrorHandler($errno, $errstr)
{
    echo '<br>error ' . $errno . ' ' . $errstr . '<br>';
}

$start = microtime(true);
//set_error_handler('ErrorHandler');
//require('../../distribution/libs/SmartyBC.class.php');
//$smarty = new SmartyBC();
require '../../distribution/libs/Smarty.class.php';
$smarty = new Smarty();
echo '<br>memory ' . memory_get_usage();
echo '<br> memory peak ' . memory_get_peak_usage() . '<br><br>';

//Smarty::muteExpectedErrors();
$smarty->php_handling = Smarty::PHP_QUOTE;
$smarty->setErrorReporting(E_ALL + E_STRICT);
error_reporting(E_ALL + E_STRICT);
//$smarty->addPluginsDir('./plugins');
$smarty->addPluginsDir('../PHPunit/PHPunitplugins');
$smarty->setPluginsDir('../PHPunit/PHPunitplugins');
//$smarty->addPluginsDir( "./../../distribution/demo/plugins/");
//$smarty->addTemplateDir('../PHPunit/templates');
//$smarty->setTemplateDir('../PHPunit/templates');
//$smarty->setConfigDir('../PHPunit/configs');
/* $smarty->setTemplateDir( array(
            'root' => '../PHPunit/templates',
            '../PHPunit/templates_2',
            '../PHPunit/templates_3',
            '../PHPunit/templates_4',
        ));
*/
//$smarty->registerCallback('trace','trace');
$smarty->php_handling = Smarty::PHP_ALLOW;
$smarty->locking_timeout = 10;
$smarty->cache_lifetime = 10;
$smarty->assign('foo',1);
//$smarty->setCaching(1);
//$smarty->debugging = true;
$smarty->force_compile = true;
/**

$smarty->display('include.tpl');

$smarty->compile_id = 12;
$smarty->assign('foo',3);
$smarty->display('include.tpl');
$smarty->cache_id = 3;
$smarty->assign('foo',2);
$smarty->display('include.tpl');
**/
$smarty->display('test_block_child_prepend.tpl');

/**
//$smarty->setCaching(1);
//$smarty->display('nocacheblockchild.tpl');
$smarty->display('test_block_child_prepend.tpl');
//$smarty->display('test_import_nocache_section.tpl');
//$smarty->display('test_recursive_includes.tpl');
//$smarty->display('test_template_function.tpl');
//$smarty->display('string:{#foo#}');
//$smarty->display('include_b.tpl');
//$smarty->display('test_block_child.tpl');
//$smarty->display('extends:test_block_parent.tpl|test_block_grandchild_e.tpl');
//$smarty->display('string:{foreach $foo as $x}{$x} {/foreach}');
//$smarty->display('eval:{debug}');
//$smarty->display('string:{$foo=1}{if isset($foo)}yes{else}no{/if}');
*/
function trace ($data)
{
    echo '<br>trace :'.$data.'<br>';
}

echo '<br>memory ' . memory_get_usage();
echo '<br> memory peak ' . memory_get_peak_usage();
echo '<br>' . (microtime(true) - $start);
