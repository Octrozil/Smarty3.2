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
class Smarty_Variable_Method_GetStreamVariable
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
    public function __construct($smarty)
    {
        $this->smarty = $smarty;
    }

    /**
     * gets  a stream variable
     *
     * @api
     * @param  string $variable the stream of the variable
     * @throws Smarty_Exception
     * @return mixed            the value of the stream variable
     */
    public function getStreamVariable($variable)
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

        if ($this->smarty->error_unassigned) {
            throw new Smarty_Exception('getStreamVariable(): Undefined stream variable "' . $variable . '"');
        } else {
            return null;
        }
    }
}