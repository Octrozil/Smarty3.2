<?php

/**
 * Smarty read include path plugin
 *
 *
 * @package CoreExtensions
 * @author Monte Ohrt
 */

/**
 * Smarty Internal Read Include Path Class
 *
 *
 * @package CoreExtensions
 */
class Smarty_Extension_Path
{

    /**
     * Return full file path from PHP include_path
     *
     * @param  Smarty $smarty   Smarty object
     * @param  string $filepath filepath
     * @return string|boolean full filepath or false
     */
    public function _getIncludePath(Smarty $smarty, $filepath)
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
