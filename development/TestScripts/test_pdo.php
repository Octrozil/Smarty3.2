<?php
/**
 * Test script for pdp
 * @author Uwe Tews
 * @package SmartyTestScripts
 */

require '../../distribution/libs/Smarty.class.php';

$smarty = new Smarty;
//$smarty->debugging = true;
//$smarty->force_compile = true;
$smarty->caching = false;
$smarty->cache_lifetime = 1000;

$dsn = 'mysql:host=localhost;dbname=test';
$login = 'Tews';
$passwd = 'Uwe';

// setting PDO to use buffered queries in mysql is
// important if you plan on using multiple result cursors
// in the template.

$db = new PDO($dsn, $login, $passwd, array(
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true));

$res = $db->prepare("select * from user");
$res->execute();
$res->setFetchMode(PDO::FETCH_LAZY);

// assign to smarty
$smarty->assign('res', $res);

$smarty->display('string:{foreach $res as $x}<br>{$x.id} {$x.user}{/foreach}{$x@total}');
