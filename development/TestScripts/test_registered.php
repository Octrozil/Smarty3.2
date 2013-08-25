<?php
/**
 * Test script for registered resources
 * @author Uwe Tews
 * @package SmartyTestScripts
 */

require '../../distribution/libs/Smarty.class.php';

$smarty = new Smarty;
error_reporting(E_ALL + E_STRICT);

// put these function somewhere in your application
function db_get_template($tpl_name, &$tpl_source, &$smarty_obj)
{
    // populating $tpl_source
    $tpl_source = '{$x="hello world"}{$x}';

    return true;
}

function db_get_timestamp($tpl_name, &$tpl_timestamp, &$smarty_obj)
{
    // $tpl_timestamp.
    $tpl_timestamp = (int) floor(time() / 100) * 100;
    var_dump($tpl_timestamp);

    return true;
}

function db_get_secure($tpl_name, &$smarty_obj)
{
    // assume all templates are secure
    return true;
}

function db_get_trusted($tpl_name, &$smarty_obj)
{
    // not used for templates
}

// register the resource name "db"
$smarty->register->resource("db", array("db_get_template",
    "db_get_timestamp",
    "db_get_secure",
    "db_get_trusted"));

//$smarty->debugging = true;
$smarty->force_compile = true;
//$smarty->caching = 1;
$smarty->cache_lifetime = 20000;

//$smarty->display('message:registered.tpl');
//$smarty->display('string:{include file=\'db:test\'}');
//$smarty->display('string:{include file=\'registered.tpl\'}');
$smarty->display('db:test');

//var_dump($smarty->resource_objects);
