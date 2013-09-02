<?php

/**
 * Smarty Extension Class Plugin
 *
 * Smarty class methods
 *
 *
 * @package CoreExtensions
 * @author Uwe Tews
 */

/**
 * Class for modifier methods
 *
 *
 * @package CoreExtensions
 */
class Smarty_Extension_Class
{

    /**
     * Registers static classes to be used in templates
     *
     * @api
     * @param  Smarty $smarty   Smarty object
     * @param  string $class_name name of class
     * @param  string $class_impl the referenced PHP class to register
     * @return Smarty
     * @throws Smarty_Exception if class does not exist
     */
    public function registerClass(Smarty $smarty, $class_name, $class_impl)
    {
        // test if exists
        if (!class_exists($class_impl, false)) {
            throw new Smarty_Exception("registerClass(): Undefined class \"{$class_impl}\"");
        }
        // register the class
        $smarty->registered_classes[$class_name] = $class_impl;

        return $smarty;
    }


    /**
     * Unregister static class
     *
     * @api
     * @param  Smarty $smarty   Smarty object
     * @param  string $class_name  name of class or null
     * @return Smarty
     */
    public function unregisterClass(Smarty $smarty, $class_name = null)
    {
        if ($class_name == null) {
            $smarty->registered_classes = array();
        } else {
            unset($smarty->registered_classes[$class_name]);
        }

        return $smarty;
    }
}
