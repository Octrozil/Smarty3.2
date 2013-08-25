<?php
require_once(dirname(__FILE__)."/../dev_settings.php");
ini_set('max_execution_time',300);
ini_set('xdebug.max_nesting_level',300);

// Create Parser
passthru("$smarty_dev_php_cli_bin ./ParserGenerator/cli.php Smarty_Compiler_Template_Php_Parser.y");

$contents = file_get_contents('Smarty_Compiler_Template_Php_Parser.php');
$contents = '<?php
/**
* Smarty Internal Plugin Template_parser
*
* This is the template parser.
* It is generated from the internal.template_parser.y file
* @package Smarty
* @subpackage Compiler
* @author Uwe Tews
*/
'.substr($contents,6);
file_put_contents('Smarty_Compiler_Template_Php_Parser.php', $contents);
copy('Smarty_Compiler_Template_Php_Parser.php','../../lib/Smarty/Compiler/Template/PHP/Parser.php');
