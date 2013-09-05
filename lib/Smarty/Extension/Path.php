<?php

/**
 * Smarty read include path plugin
 *
 *
 * @package Smarty
 * @author Monte Ohrt
 */

/**
 * Smarty Internal Read Include Path Class
 *
 *
 * @package Smarty
 */
class Smarty_Extension_Path
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
     * Return full file path from PHP include_path
     *
     * @param  string $filepath filepath
     * @return string|boolean full filepath or false
     */
    public function _getIncludePath($filepath)
    {
        static $_include_path_array = null;

        if ($_include_path_array === null) {
            $_include_path_array = explode(PATH_SEPARATOR, get_include_path());
        }

        foreach ($_include_path_array as $_path) {
            if (file_exists($_path . '/' . $filepath)) {
                return $_path . '/' . $filepath;
            }
        }

        return false;
    }
}
