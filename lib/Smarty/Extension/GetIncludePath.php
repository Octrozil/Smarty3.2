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
 * Class for getIncludePath method
 *
 * @package Smarty\Extension
 */
class Smarty_Extension_GetIncludePath
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
     * @internal
     * @param  string $filepath filepath
     * @return string|boolean full filepath or false
     */
    public function getIncludePath($filepath)
    {
        static $_include_path_array = null;

        if ($_include_path_array === null) {
            $_include_path_array = explode(PATH_SEPARATOR, get_include_path());
        }

        foreach ($_include_path_array as $_path) {
            if (is_file($_path . '/' . $filepath)) {
                return $_path . '/' . $filepath;
            }
        }

        return false;
    }
}
