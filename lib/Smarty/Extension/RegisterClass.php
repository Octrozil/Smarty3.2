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
 * Class for registerClass method
 *
 * @package Smarty\Extension
 */
class Smarty_Extension_RegisterClass
{
    /**
     *  Smarty object
     *
     * @var Smarty
     */
    public $smarty;

    /**
     *  Constructor
     *
     * @param Smarty $smarty Smarty object
     */
    public function __construct(Smarty $smarty)
    {
        $this->smarty = $smarty;
    }


    /**
     * Registers static classes to be used in templates
     *
     * @api
     * @param  string $class_name name of class
     * @param  string $class_impl the referenced PHP class to register
     * @return Smarty
     * @throws Smarty_Exception if class does not exist
     */
    public function registerClass($class_name, $class_impl)
    {
        // test if exists
        if (!class_exists($class_impl, false)) {
            throw new Smarty_Exception("registerClass(): Undefined class \"{$class_impl}\"");
        }
        // register the class
        $this->smarty->registered_classes[$class_name] = $class_impl;

        return $this->smarty;
    }
}
