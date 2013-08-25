<?php
/**
 * Test script for resource plugins
 * @author Uwe Tews
 * @package SmartyTestScripts
 */

function smarty_resource_core_source($tpl_name, &$tpl_source, $smarty)
{
    // do database call here to fetch your template,
    // populating $tpl_source
    $tpl_source = '{$x="hello world"}{$x} dir {$smarty.current_dir} template {$smarty.template}';

    return true;
}

function smarty_resource_core_timestamp($tpl_name, &$tpl_timestamp, $smarty)
{
    // $tpl_timestamp.
    $tpl_timestamp = 100;

    return true;
}

function smarty_resource_core_secure($tpl_name, $smarty)
{
    // assume all templates are secure
    return true;
}

function smarty_resource_core_trusted($tpl_name, $smarty)
{
    // not used for templates
}

require '../../distribution/libs/Smarty.class.php';
error_reporting(E_ALL);

$smarty = new Smarty;
$smarty->error_reporting = (E_ALL);
$smarty->deprecation_notices = false;
if (false) {
    $smarty->registerPlugin(Smarty::PLUGIN_RESOURCE, "core", array("smarty_resource_core_source",
        "smarty_resource_core_timestamp",
        "smarty_resource_core_secure",
        "smarty_resource_core_trusted"));
} else {
    $smarty->registerresource("core", array("smarty_resource_core_source",
        "smarty_resource_core_timestamp",
        "smarty_resource_core_secure",
        "smarty_resource_core_trusted"));
}
//$smarty->default_resource_type = 'core';
//$smarty->debugging = true;
$smarty->force_compile = true;
$smarty->caching = 1;
$smarty->cache_lifetime = 1000;
$smarty->plugins_dir[] = './plugins';

//if ($smarty->templateExists('core:test')) echo 'ist da '; else echo 'nein ';
//$smarty->display('message:registered.tpl');
//$tpl=$smarty->createTemplate('core:test');
//$smarty->display('extends:test_block.tpl|core:test');
$smarty->display('extends:core:test|test_block.tpl');
//$smarty->display('string:{include file=\'registered.tpl\'}');
//$smarty->display('bug.tpl');

//var_dump($smarty->resource_objects);
