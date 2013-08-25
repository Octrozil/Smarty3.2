<?php
/**
 * Test script for nocache sections
 * @author Uwe Tews
 * @package SmartyTestScripts
 */

require '../../distribution/libs/Smarty.class.php';
error_reporting(E_ALL);

$smarty = new Smarty;
$tpl = $smarty->createTemplate('include.tpl');
var_dump(Smarty::instance());

$smarty2 = Smarty::createClone();
var_dump(Smarty::instance());

Smarty::destruct();
var_dump(Smarty::instance());

Smarty::destruct();
