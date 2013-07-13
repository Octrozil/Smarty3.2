<?php
require_once(dirname(__FILE__)."/../dev_settings.php");
ini_set('max_execution_time',300);
ini_set('xdebug.max_nesting_level',300);

// Create Lexer
require_once './LexerGenerator.php';
$lex = new PHP_LexerGenerator('Smarty_Compiler_Template_Lexer.plex');
$contents = file_get_contents('Smarty_Compiler_Template_Lexer.php');
$contents = str_replace(array('SMARTYldel','SMARTYrdel'),array('".$this->ldel."','".$this->rdel."'),$contents);
file_put_contents('Smarty_Compiler_Template_Lexer.php', substr($contents, 0 , strlen($contents)-2));
copy('Smarty_Compiler_Template_Lexer.php','../../lib/Smarty/Compiler/Template/Lexer.php');

// Create Parser
passthru("$smarty_dev_php_cli_bin ./ParserGenerator/cli.php Smarty_Compiler_Template_Parser.y");

$contents = file_get_contents('Smarty_Compiler_Template_Parser.php');
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
file_put_contents('Smarty_Compiler_Template_Parser.php', $contents);
copy('Smarty_Compiler_Template_Parser.php','../../lib/Smarty/Compiler/Template/Parser.php');
