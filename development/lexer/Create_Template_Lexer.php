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

