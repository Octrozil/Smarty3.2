@echo off
REM PHP_CodeSniffer tokenises PHP code and detects violations of a
REM defined set of coding standards.
REM
REM PHP version 5
REM
REM @category  PHP
REM @package   PHP_CodeSniffer
REM @author    Greg Sherwood <gsherwood@squiz.net>
REM @author    Marc McIntyre <mmcintyre@squiz.net>
REM @copyright 2006-2011 Squiz Pty Ltd (ABN 77 084 670 600)
REM @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
REM @version   CVS: $Id: phpcs.bat,v 1.3 2007-11-04 22:02:16 squiz Exp $
REM @link      http://pear.php.net/package/PHP_CodeSniffer

"C:\wamp\bin\php\php5.2.9-1\.\php.exe" -d auto_append_file="" -d auto_prepend_file="" -d include_path="C:\wamp\bin\php\php5.3.8\pear" "C:\wamp\bin\php\php5.3.8\phpcs" --report-file="sniffer.txt" c:\wamp\www\smarty3.2.cm\distribution\libs\
