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
 * Autoloads Smarty classes.
 *
 * @package Smarty
 */
class Smarty_Autoloader
{
    /**
     * @var string realpath of smarty distribution
     */
    public  $smartyBaseDir = null;
    public static $Smarty_Dir = null;

    /**
     * @var array of class names with special format
     */
    public static $rootClasses = array('Smarty' => 'Smarty/Smarty', 'SmartyBC' => 'Smarty/SmartyBC', 'SmartyBC31' => 'Smarty/SmartyBC31');

    /**
     * Autoloader constructor.
     *
     * @param string $baseDir root of Smarty library
     */
    public function __construct($baseDir = null)
    {
        if ($baseDir === null) {
            self::$Smarty_Dir = $this->smartyBaseDir = dirname(__FILE__).'/../';
        } else {
            self::$Smarty_Dir = $this->smartyBaseDir = rtrim($baseDir, '/') . '/';
        }
    }
    /**
     * Registers Smarty_Autoloader as an SPL autoloader.
     *
     * @param Boolean $prepend Whether to prepend the autoloader or not.
     */
    public static function register($baseDir = null, $prepend = false)
    {
        $loader = new self($baseDir);
        if (version_compare(phpversion(), '5.3.0', '>=')) {
            spl_autoload_register(array($loader, 'autoload'), true, $prepend);
        } else {
            spl_autoload_register(array($loader, 'autoload'));
        }
        return $loader;
    }

    /**
     * Handles autoloading of classes.
     *
     * This function can also be called manually
     *
     * @param string $class          class name.
     */
    public static function autoload($class)
    {
        if (0 !== strpos($class, 'Smarty')) {
            return;
        }
        if (isset(self::$rootClasses[$class])) {
            $file = self::$Smarty_Dir . self::$rootClasses[$class] . '.php';
        } else {
            $file = self::$Smarty_Dir . str_replace('_', '/', $class) . '.php';
        }
        if (is_file($file)) {
            require $file;
        }
    }
}
