<?php
require_once(dirname(__FILE__)."/../dev_settings.php");
// Create Lexer
require_once './LexerGenerator.php';
$lex = new PHP_LexerGenerator('Smarty_Compiler_Config_Lexer.plex');
$contents = file_get_contents('Smarty_Compiler_Config_Lexer.php');
file_put_contents('Smarty_Compiler_Config_Lexer.php', substr($contents, 0 , strlen($contents)-2));
copy('Smarty_Compiler_Config_Lexer.php','../../lib/Smarty/Compiler/Config/Lexer.php');

// Create Parser
passthru("$smarty_dev_php_cli_bin ./ParserGenerator/cli.php Smarty_Compiler_Config_Parser.y");

$contents = file_get_contents('Smarty_Compiler_Config_Parser.php');
$contents = '<?php
/**
* Smarty Internal Plugin Configfile Parser
*
* This is the config file parser.
* It is generated from the internal.configfile_parser.y file
* @package Smarty
* @subpackage Compiler
* @author Uwe Tews
*/
'.substr($contents,6);
file_put_contents('Smarty_Compiler_Config_Parser.php', $contents);
copy('Smarty_Compiler_Config_Parser.php','../../lib/Smarty/Compiler/Config/Parser.php');
