<?php

/**
 * Smarty Autoloader
 *
 * This file contains the Smarty autoloader class
 *
 *
 * @package Smarty
 * @author Uwe Tews
 */

/**
 * set SMARTY_DIR to absolute path to Smarty library files.
 * Sets SMARTY_DIR only if user application has not already defined it.
 */
if (!defined('SMARTY_DIR')) {
    define('SMARTY_DIR', dirname(__FILE__) . '/../');
}

/**
 * Autoloads Smarty classes.
 *
 * @package Smarty
 */
class Smarty_Autoloader
{
    /**
     * @var string realpath of smarty distribution
     */
    public static $smarty_path = null;
    /**
     * @var array of class names with special format
     */
    public static $rootClasses = array('Smarty' => 'Smarty/Smarty', 'SmartyBC' => 'Smarty/SmartyBC', 'SmartyBC3' => 'Smarty/SmartyBC3');
    public static $checkFile = true;

    /**
     * Registers Smarty_Autoloader as an SPL autoloader.
     *
     * @param Boolean $prepend Whether to prepend the autoloader or not.
     */
    public static function register($prepend = false)
    {
        self::$smarty_path = realpath(SMARTY_DIR) . '/';
        if (version_compare(phpversion(), '5.3.0', '>=')) {
            spl_autoload_register(array(new self, 'autoload'), true, $prepend);
        } else {
            spl_autoload_register(array(new self, 'autoload'));
        }
    }

    /**
     * Handles autoloading of classes.
     * 
     * This function can also be called manually
     * 
     * @param string    $class          class name.
     * @param bool      $check          this optional parameter must be set if autoload function is call manually
    */
    public static function autoload($class, $check = false)
    {
        if (0 !== strpos($class, 'Smarty')) {
            return;
        }
        if (isset(self::$rootClasses[$class])) {
            $file = self::$smarty_path . self::$rootClasses[$class] . '.php';
        } else {
            $file = self::$smarty_path . str_replace('_', '/', $class) . '.php';
        }
        if ((!(self::$checkFile || $check)) || file_exists($file)) {
            require $file;
            if ($check) {
                return true;
            }
        }
    }
}

/**
 *  Register Smarty autoloader
 */
Smarty_Autoloader::register();
