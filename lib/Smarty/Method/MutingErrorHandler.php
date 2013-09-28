<?php

/**
 * Smarty Extension
 *
 * Smarty class methods
 *
 * @package Smarty\Extension
 * @author Uwe Tews
 */

/**
 * Class for static mutingErrorHandler method
 *
 * @package Smarty\Extension
 */
class Smarty_Method_MutingErrorHandler
{
    private static $has_smarty_dir = false;

    /**
     * Error Handler to mute expected messages
     *
     * @api
     * @link http://php.net/set_error_handler
     * @param integer $errno Error level
     * @param $errstr
     * @param $errfile
     * @param $errline
     * @param $errcontext
     * @return boolean
     */
    public static function mutingErrorHandler($errno, $errstr, $errfile, $errline, $errcontext)
    {
        if (!self::$has_smarty_dir) {
            // add the SMARTY_DIR to the list of muted directories
            $smarty_dir = realpath(Smarty_Autoloader::$Smarty_Dir);
            if ($smarty_dir !== false) {
                Smarty::$_muted_directories[Smarty_Autoloader::$Smarty_Dir] = array(
                    'file' => $smarty_dir,
                    'length' => strlen($smarty_dir),
                );
            }
            self::$has_smarty_dir = true;
        }

        $_is_muted_directory = false;

        // add the SMARTY_DIR to the list of muted directories
        if (!isset(Smarty::$_muted_directories[Smarty_Autoloader::$Smarty_Dir])) {
            $smarty_dir = realpath(Smarty_Autoloader::$Smarty_Dir);
            if ($smarty_dir !== false) {
                Smarty::$_muted_directories[Smarty_Autoloader::$Smarty_Dir] = array(
                    'file' => $smarty_dir,
                    'length' => strlen($smarty_dir),
                );
            }
        }

        // walk the muted directories and test against $errfile
        foreach (Smarty::$_muted_directories as $key => &$dir) {
            if (!$dir) {
                // resolve directory and length for speedy comparisons
                $file = realpath($key);
                if ($file === false) {
                    // this directory does not exist, remove and skip it
                    unset(Smarty::$_muted_directories[$key]);
                    continue;
                }
                $dir = array(
                    'file' => $file,
                    'length' => strlen($file),
                );
            }
            if (strpos($errfile, $dir['file']) === 0) {
                $_is_muted_directory = true;
                break;
            }
        }

        // pass to next error handler if this error did not occur inside Smarty_Autoloader::$smarty_path
        // or the error was within smarty but masked to be ignored
        if (!$_is_muted_directory || ($errno && $errno & error_reporting())) {
            if (Smarty::$_previous_error_handler) {
                return call_user_func(Smarty::$_previous_error_handler, $errno, $errstr, $errfile, $errline, $errcontext);
            } else {
                return false;
            }
        }
    }
}
