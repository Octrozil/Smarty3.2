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
     * @param string $class A class name.
     */
    public static function autoload($class)
    {
        static $_rootClasses = array('Smarty' => true, 'SmartyBC' => true, 'SmartyBC3' => true);
        if (0 !== strpos($class, 'Smarty')) {
            return;
        }
        if (isset($_rootClasses[$class])) {
            require self::$smarty_path . 'Smarty/' . $class . '.php';
        } else {
            require self::$smarty_path . str_replace('_', '/', $class) . '.php';
//        require self::$smarty_path . str_replace(array('_', "\0"), array('/', ''), $class).'.php';
        }
    }
}

/**
 *  Register Smarty autoloader
 */
Smarty_Autoloader::register();
