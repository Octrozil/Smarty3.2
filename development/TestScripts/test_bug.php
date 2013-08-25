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
require_once '../../lib/Smarty/Autoloader.php';
$smarty = new Smarty();
$time = microtime(true);
echo '<br>startup time '. ($time-$start);
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
$smarty->registerCallback('trace','trace');
//$smarty->testInstall();
//$smarty->addTemplateDir('./templates/bug');
//$smarty->cache_dir=realpath($smarty->cache_dir);
//$smarty->caching_type = 'apc';
//
$smarty->php_handling = Smarty::PHP_ALLOW;
//$smarty->auto_literal = false;
//$smarty->compile_check = Smarty::COMPILECHECK_CACHEMISS;
// $smarty->cache_lifetime = -1;
//$smarty->debugging_ctrl = 'URL';
//$smarty->debug_tpl = 'file:' . SMARTY_DIR . 'dSmarty.tpl';
//$smarty->debugging = true;
// $smarty->default_modifiers = array('escape:"htmlall"','strlen');
// $smarty->use_sub_dirs = true;
// $smarty->setCacheLifetime(10);
//$smarty->cache_modified_check = true;
// $smarty->security=true;;
// $smarty->enableSecurity();
// $smarty->loadFilter('output', 'trimwhitespace');
// $smarty->loadFilter("variable", "htmlspecialchars");
// $smarty->loadFilter('pre', 'translate');
// $smarty->display('child.tpl');
//$smarty->clearAllCache();
// echo $smarty->clear_cache(null,'uwe');
// $smarty->compile_id = 'bar';
//$smarty->cache_id = 'uwe|tews';
// $smarty->left_delimiter = '[%';
// $smarty->right_delimiter = '%]';
//$smarty->use_sub_dirs = true;
//$smarty->error_unassigned = Smarty::UNASSIGNED_EXCEPTION;
//$smarty->loadFilter('post', 'testuwe');
//$smarty->enableSecurity();
//$smarty->security_policy->disabled_tags=array('cycle');
//$smarty->security_policy->allowed_tags=array('counter');
//$smarty->cache_locking = true;
//$smarty->_parserdebug = true;
$smarty->locking_timeout = 10;
//$smarty->setCaching(1);
$smarty->assign('foo', 1, true);
$smarty->assign('values', 1, true);
$smarty->force_compile = true;
$smarty->display('eval:{foreach $foo as $j} {$hh{$j}hh} {/foreach}');
//$smarty->display('bug.tpl');
//$smarty->display('bug4.tpl');

//$smarty->compile_check = false;
//$smarty->force_cache = true;
//$smarty->merge_compiled_includes = true;
/**
$smarty->assign('foo', 1);
$smarty->assign('values', array(1,2,3,4));
$smarty->display('loop-include.tpl');
$smarty->assign('values', 1);
$smarty->display('include0.tpl');
//$smarty->display('include.tpl');
*/

/**
$smarty->assign('foo', 1);
$data = $smarty->createData($smarty);
$data->assign('bar','bar');
//$data->assign('foo','bar');
$tpl = $smarty->createTemplate ('include.tpl', $data);
$tpl->display();
$tpl->assign('foo', 2);
$tpl->assign('ha', 2);
$tpl->display();
$tpl->display();
$tpl->assign('foo', 3);
$tpl->display();
$smarty->display('include.tpl');
$i = 3;
*/


//$smarty->assign('bar','bar');
//$smarty->setCaching(1);
//$smarty->display('helloworld.tpl');
//$smarty->display('db:test');
//$smarty->force_compile = true;
//$smarty->display('nocacheblockchild.tpl');
//$smarty->display('include2.tpl');
//$smarty->display('test_block_child_prepend.tpl');
//$smarty->display('test_import_nocache_section.tpl');
//$smarty->display('test_recursive_includes.tpl');
//$smarty->display('test_template_function.tpl');
//$smarty->display('string:{#foo#}');
//$smarty->display('include_b.tpl');
//$smarty->display('test_block_child.tpl');
//$smarty->display('extends:test_block_parent.tpl|test_block_grandchild_e.tpl');
//$smarty->display('string:{foreach $foo as $x}{$x} {/foreach}');
//$smarty->display('eval:{assign var=foo value=bar}{capture assign=foo}hello world{/capture}{$foo|escape}');
//$smarty->display('string:{$foo=1}{if isset($foo)}yes{else}no{/if}');

function trace ($data)
{
    echo '<br>trace :'.$data.'<br>';
}

echo '<br>memory ' . memory_get_usage();
echo '<br> memory peak ' . memory_get_peak_usage();
echo '<br>' . (microtime(true) - $start);
echo '<br>' . (microtime(true) - $time);
