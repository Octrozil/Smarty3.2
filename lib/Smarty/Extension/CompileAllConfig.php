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
 * Class for compileAllConfig method
 *
 * @package Smarty\Extension
 */
class Smarty_Extension_CompileAllConfig
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
     * @param Smarty $smarty
     */
    public function __construct(Smarty $smarty)
    {
        $this->smarty = $smarty;
    }

    /**
     * Compile all config files
     *
     * @api
     * @param  string $extension     extension of config file names
     * @param  bool $force_compile force all to recompile
     * @param  int $time_limit    set maximum execution time
     * @param  int $max_errors    set maximum allowed errors
     * @return integer number of config files compiled
     */
    public function compileAllConfig($extension, $force_compile, $time_limit, $max_errors)
    {
        // switch off time limit
        if (function_exists('set_time_limit')) {
            @set_time_limit($time_limit);
        }
        $this->smarty->force_compile = $force_compile;
        $_count = 0;
        $_error_count = 0;
        // loop over array of template directories
        foreach ($this->smarty->getConfigDir() as $_dir) {
            $_compileDirs = new RecursiveDirectoryIterator($_dir);
            $_compile = new RecursiveIteratorIterator($_compileDirs);
            foreach ($_compile as $_fileinfo) {
                $_file = $_fileinfo->getFilename();
                if (substr(basename($_fileinfo->getPathname()), 0, 1) == '.' || strpos($_file, '.svn') !== false)
                    continue;
                if (!substr_compare($_file, $extension, -strlen($extension)) == 0)
                    continue;
                if ($_fileinfo->getPath() == substr($_dir, 0, -1)) {
                    $_config_file = $_file;
                } else {
                    $_config_file = substr($_fileinfo->getPath(), strlen($_dir)) . '/' . $_file;
                }
                echo '<br>', $_dir, '---', $_config_file;
                flush();
                $_start_time = microtime(true);
                try {
                    $_tpl = $this->smarty->createTemplate($_config_file);
                    $_tpl->_usage = Smarty::IS_CONFIG;
                    if ($_tpl->mustCompile) {
                        $_tpl->compiler->compileTemplateSource();
                        $_tpl->cleanPointer();
                        $_count++;
                        echo ' compiled in  ', microtime(true) - $_start_time, ' seconds';
                        echo '<br>' . memory_get_usage(true);
                        flush();
                    } else {
                        echo ' is up to date';
                        flush();
                    }
                } catch (Exception $e) {
                    echo 'Error: ', $e->getMessage(), "<br><br>";
                    $_error_count++;
                }
                // free memory
                Smarty::$resource_cache = array();
                Smarty::$template_cache = array();
                $_tpl = null;
                if ($max_errors !== null && $_error_count == $max_errors) {
                    echo '<br><br>too many errors';
                    exit();
                }
            }
        }

        return $_count;
    }
}
