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
 * Class for unregisterClass method
 *
 * @package Smarty\Extension
 */
class Smarty_Extension_UnregisterClass
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
     * Unregister static class
     *
     * @api
     * @param  string $class_name  name of class or null
     * @return Smarty
     */
    public function unregisterClass($class_name = null)
    {
        if ($class_name == null) {
            $this->smarty->_registered['class'] = array();
        } else {
            unset($this->smarty->_registered['class'][$class_name]);
        }

        return $this->smarty;
    }
}
