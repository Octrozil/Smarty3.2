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
 * Class for static getStreamVariable method
 *
 * @package Smarty\Extension
 */
class Smarty_Variable_Extension_GetStreamVariable
{

    /**
     * gets  a stream variable
     *
     * @internal
     * @param  Smarty $smarty  object
     * @param  string $variable the stream of the variable
     * @throws Smarty_Exception
     * @return mixed            the value of the stream variable
     */
    public static function getStreamVariable(Smarty $smarty, $variable)
    {
        $_result = '';
        $fp = fopen($variable, 'r+');
        if ($fp) {
            while (!feof($fp) && ($current_line = fgets($fp)) !== false) {
                $_result .= $current_line;
            }
            fclose($fp);

            return $_result;
        }

        if ($smarty->error_unassigned) {
            throw new Smarty_Exception('getStreamVariable(): Undefined stream variable "' . $variable . '"');
        } else {
            return null;
        }
    }
}