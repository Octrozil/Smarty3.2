<?php

/**
 * Project:     Smarty: the PHP compiling template engine
 * File:        Smarty_Misc_Utility.php
 * SVN:         $Id: $
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * For questions, help, comments, discussion, etc., please join the
 * Smarty mailing list. Send a blank e-mail to
 * smarty-discussion-subscribe@googlegroups.com
 *
 * @link http://www.smarty.net/
 * @copyright 2008 New Digital Group, Inc.
 * @author Monte Ohrt <monte at ohrt dot com>
 * @author Uwe Tews
 *
 * @package PluginsInternal
 * @version 3-SVN$Rev: 3286 $
 */

/**
 * Utility class
 *
 *
 * @package Security
 */
class Smarty_Misc_Utility
{

    /**
     * private constructor to prevent calls creation of new instances
     */
    final private function __construct()
    {
        // intentionally left blank
    }

    /**
     * Compile all template files
     *
     * @param string  $extension     extension of template file names
     * @param boolean $force_compile true to force recompilation of all templates
     * @param int     $time_limit    set maximum execution time
     * @param int     $max_errors    set maximum allowed errors
     * @param Smarty  $smarty        Smarty instance
     * @internal param string $extension template file name extension
     * @return integer number of template files compiled
     */
    public static function compileAllTemplates($extension, $force_compile, $time_limit, $max_errors, Smarty $smarty)
    {
        // switch off time limit
        if (function_exists('set_time_limit')) {
            @set_time_limit($time_limit);
        }
        $smarty->force_compile = $force_compile;
        $_count = 0;
        $_error_count = 0;
        // loop over array of template directories
        foreach ($smarty->getTemplateDir() as $_dir) {
            $_compileDirs = new RecursiveDirectoryIterator($_dir);
            $_compile = new RecursiveIteratorIterator($_compileDirs);
            foreach ($_compile as $_fileinfo) {
                $_file = $_fileinfo->getFilename();
                if (substr(basename($_fileinfo->getPathname()), 0, 1) == '.' || strpos($_file, '.svn') !== false)
                    continue;
                if (!substr_compare($_file, $extension, -strlen($extension)) == 0)
                    continue;
                if ($_fileinfo->getPath() == substr($_dir, 0, -1)) {
                    $_template_file = $_file;
                } else {
                    $_template_file = substr($_fileinfo->getPath(), strlen($_dir)) . DS . $_file;
                }
                echo '<br>', $_dir, '---', $_template_file;
                flush();
                $_start_time = microtime(true);
                try {
                    $_tpl = $smarty->createTemplate($_template_file);
                    if ($_tpl->mustCompile) {
                        $_tpl->compiler->compileTemplateSource();
                        $_tpl->cleanPointer();
                        $_count++;
                        echo ' compiled in  ', microtime(true) - $_start_time, ' seconds';
                        flush();
                        echo '<br>' . memory_get_usage(true);
                    } else {
                        echo ' is up to date';
                        flush();
                    }
                } catch (Exception $e) {
                    echo 'Error: ', $e->getMessage(), "<br><br>";
                    $_error_count++;
                }
                // free memory
                Smarty::$_source_cache = array();
                $_tpl = null;
                if ($max_errors !== null && $_error_count == $max_errors) {
                    echo '<br><br>too many errors';
                    exit();
                }
            }
        }

        return $_count;
    }

    /**
     * Compile all config files
     *
     * @param  string  $extension     extension of config file names
     * @param  bool    $force_compile force all to recompile
     * @param  int     $time_limit    set maximum execution time
     * @param  int     $max_errors    set maximum allowed errors
     * @param  Smarty  $smarty        Smarty instance
     * @return integer number of config files compiled
     */
    public static function compileAllConfig($extension, $force_compile, $time_limit, $max_errors, Smarty $smarty)
    {
        // switch off time limit
        if (function_exists('set_time_limit')) {
            @set_time_limit($time_limit);
        }
        $smarty->force_compile = $force_compile;
        $_count = 0;
        $_error_count = 0;
        // loop over array of template directories
        foreach ($smarty->getConfigDir() as $_dir) {
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
                    $_config_file = substr($_fileinfo->getPath(), strlen($_dir)) . DS . $_file;
                }
                echo '<br>', $_dir, '---', $_config_file;
                flush();
                $_start_time = microtime(true);
                try {
                    $_tpl = $smarty->createTemplate($_config_file);
                    $_tpl->usage = Smarty::IS_CONFIG;
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
                Smarty::$_source_cache = array();
                $_tpl = null;
                if ($max_errors !== null && $_error_count == $max_errors) {
                    echo '<br><br>too many errors';
                    exit();
                }
            }
        }

        return $_count;
    }

    /**
     * Return array of tag/attributes of all tags used by an template
     *
     * @param  Smarty $template template object
     * @return array  of tag/attributes
     */
    public static function getTags(Smarty $template)
    {
        $tpl_obj->get_used_tags = true;
        $tpl_obj->compiler->compileTemplateSource();
        unset($tpl_obj->compiler);

        return $tpl_obj->used_tags;
    }

    /**
     * diagnose Smarty setup
     *
     * If $errors is secified, the diagnostic report will be appended to the array, rather than being output.
     *
     * @param  Smarty $smarty Smarty instance to test
     * @param  array  $errors array to push results into rather than outputting them
     * @return bool   status, true if everything is fine, false else
     */
    public static function testInstall(Smarty $smarty, &$errors = null)
    {
        $status = true;

        if ($errors === null) {
            echo "<PRE>\n";
            echo "Smarty Installation test...\n";
            echo "Testing template directory...\n";
        }

        $_stream_resolve_include_path = function_exists('stream_resolve_include_path');
        // test if all registered template_dir are accessible
        foreach ($smarty->getTemplateDir() as $template_dir) {
            $_template_dir = $template_dir;
            $template_dir = realpath($template_dir);
            // resolve include_path or fail existance
            if (!$template_dir) {
                if ($smarty->use_include_path && !preg_match('/^([\/\\\\]|[a-zA-Z]:[\/\\\\])/', $_template_dir)) {
                    // try PHP include_path
                    if ($_stream_resolve_include_path) {
                        $template_dir = stream_resolve_include_path($_template_dir);
                    } else {
                        $template_dir = Smarty_Misc_GetIncludePath::getIncludePath($_template_dir);
                    }
                    if ($template_dir !== false) {
                        if ($errors === null) {
                            echo "$template_dir is OK.\n";
                        }

                        continue;
                    } else {
                        $status = false;
                        $message = "FAILED: $_template_dir does not exist (and couldn't be found in include_path either)";
                        if ($errors === null) {
                            echo $message . ".\n";
                        } else {
                            $errors['template_dir'] = $message;
                        }

                        continue;
                    }
                } else {
                    $status = false;
                    $message = "FAILED: $_template_dir does not exist";
                    if ($errors === null) {
                        echo $message . ".\n";
                    } else {
                        $errors['template_dir'] = $message;
                    }

                    continue;
                }
            }

            if (!is_dir($template_dir)) {
                $status = false;
                $message = "FAILED: $template_dir is not a directory";
                if ($errors === null) {
                    echo $message . ".\n";
                } else {
                    $errors['template_dir'] = $message;
                }
            } elseif (!is_readable($template_dir)) {
                $status = false;
                $message = "FAILED: $template_dir is not readable";
                if ($errors === null) {
                    echo $message . ".\n";
                } else {
                    $errors['template_dir'] = $message;
                }
            } else {
                if ($errors === null) {
                    echo "$template_dir is OK.\n";
                }
            }
        }

        if ($errors === null) {
            echo "Testing compile directory...\n";
        }

        // test if registered compile_dir is accessible
        $__compile_dir = $smarty->getCompileDir();
        $_compile_dir = realpath($__compile_dir);
        if (!$_compile_dir) {
            $status = false;
            $message = "FAILED: {$__compile_dir} does not exist";
            if ($errors === null) {
                echo $message . ".\n";
            } else {
                $errors['compile_dir'] = $message;
            }
        } elseif (!is_dir($_compile_dir)) {
            $status = false;
            $message = "FAILED: {$_compile_dir} is not a directory";
            if ($errors === null) {
                echo $message . ".\n";
            } else {
                $errors['compile_dir'] = $message;
            }
        } elseif (!is_readable($_compile_dir)) {
            $status = false;
            $message = "FAILED: {$_compile_dir} is not readable";
            if ($errors === null) {
                echo $message . ".\n";
            } else {
                $errors['compile_dir'] = $message;
            }
        } elseif (!is_writable($_compile_dir)) {
            $status = false;
            $message = "FAILED: {$_compile_dir} is not writable";
            if ($errors === null) {
                echo $message . ".\n";
            } else {
                $errors['compile_dir'] = $message;
            }
        } else {
            if ($errors === null) {
                echo "{$_compile_dir} is OK.\n";
            }
        }

        if ($errors === null) {
            echo "Testing plugins directory...\n";
        }

        // test if all registered plugins_dir are accessible
        // and if core plugins directory is still registered
        $_core_plugins_dir = realpath(dirname(__FILE__) . '/../plugins');
        $_core_plugins_available = false;
        foreach ($smarty->getPluginsDir() as $plugin_dir) {
            $_plugin_dir = $plugin_dir;
            $plugin_dir = realpath($plugin_dir);
            // resolve include_path or fail existance
            if (!$plugin_dir) {
                if ($smarty->use_include_path && !preg_match('/^([\/\\\\]|[a-zA-Z]:[\/\\\\])/', $_plugin_dir)) {
                    // try PHP include_path
                    if ($_stream_resolve_include_path) {
                        $plugin_dir = stream_resolve_include_path($_plugin_dir);
                    } else {
                        $plugin_dir = Smarty_Misc_GetIncludePath::getIncludePath($_plugin_dir);
                    }
                    if ($plugin_dir !== false) {
                        if ($errors === null) {
                            echo "$plugin_dir is OK.\n";
                        }

                        continue;
                    } else {
                        $status = false;
                        $message = "FAILED: $_plugin_dir does not exist (and couldn't be found in include_path either)";
                        if ($errors === null) {
                            echo $message . ".\n";
                        } else {
                            $errors['plugins_dir'] = $message;
                        }

                        continue;
                    }
                } else {
                    $status = false;
                    $message = "FAILED: $_plugin_dir does not exist";
                    if ($errors === null) {
                        echo $message . ".\n";
                    } else {
                        $errors['plugins_dir'] = $message;
                    }

                    continue;
                }
            }

            if (!is_dir($plugin_dir)) {
                $status = false;
                $message = "FAILED: $plugin_dir is not a directory";
                if ($errors === null) {
                    echo $message . ".\n";
                } else {
                    $errors['plugins_dir'] = $message;
                }
            } elseif (!is_readable($plugin_dir)) {
                $status = false;
                $message = "FAILED: $plugin_dir is not readable";
                if ($errors === null) {
                    echo $message . ".\n";
                } else {
                    $errors['plugins_dir'] = $message;
                }
            } elseif ($_core_plugins_dir && $_core_plugins_dir == realpath($plugin_dir)) {
                $_core_plugins_available = true;
                if ($errors === null) {
                    echo "$plugin_dir is OK.\n";
                }
            } else {
                if ($errors === null) {
                    echo "$plugin_dir is OK.\n";
                }
            }
        }
        if (!$_core_plugins_available) {
            $status = false;
            $message = "WARNING: Smarty's own libs/plugins is not available";
            if ($errors === null) {
                echo $message . ".\n";
            } elseif (!isset($errors['plugins_dir'])) {
                $errors['plugins_dir'] = $message;
            }
        }

        if ($errors === null) {
            echo "Testing cache directory...\n";
        }

        // test if all registered cache_dir is accessible
        $__cache_dir = $smarty->getCacheDir();
        $_cache_dir = realpath($__cache_dir);
        if (!$_cache_dir) {
            $status = false;
            $message = "FAILED: {$__cache_dir} does not exist";
            if ($errors === null) {
                echo $message . ".\n";
            } else {
                $errors['cache_dir'] = $message;
            }
        } elseif (!is_dir($_cache_dir)) {
            $status = false;
            $message = "FAILED: {$_cache_dir} is not a directory";
            if ($errors === null) {
                echo $message . ".\n";
            } else {
                $errors['cache_dir'] = $message;
            }
        } elseif (!is_readable($_cache_dir)) {
            $status = false;
            $message = "FAILED: {$_cache_dir} is not readable";
            if ($errors === null) {
                echo $message . ".\n";
            } else {
                $errors['cache_dir'] = $message;
            }
        } elseif (!is_writable($_cache_dir)) {
            $status = false;
            $message = "FAILED: {$_cache_dir} is not writable";
            if ($errors === null) {
                echo $message . ".\n";
            } else {
                $errors['cache_dir'] = $message;
            }
        } else {
            if ($errors === null) {
                echo "{$_cache_dir} is OK.\n";
            }
        }

        if ($errors === null) {
            echo "Testing configs directory...\n";
        }

        // test if all registered config_dir are accessible
        foreach ($smarty->getConfigDir() as $config_dir) {
            $_config_dir = $config_dir;
            $config_dir = realpath($config_dir);
            // resolve include_path or fail existance
            if (!$config_dir) {
                if ($smarty->use_include_path && !preg_match('/^([\/\\\\]|[a-zA-Z]:[\/\\\\])/', $_config_dir)) {
                    // try PHP include_path
                    if ($_stream_resolve_include_path) {
                        $config_dir = stream_resolve_include_path($_config_dir);
                    } else {
                        $config_dir = Smarty_Misc_GetIncludePath::getIncludePath($_config_dir);
                    }
                    if ($config_dir !== false) {
                        if ($errors === null) {
                            echo "$config_dir is OK.\n";
                        }

                        continue;
                    } else {
                        $status = false;
                        $message = "FAILED: $_config_dir does not exist (and couldn't be found in include_path either)";
                        if ($errors === null) {
                            echo $message . ".\n";
                        } else {
                            $errors['config_dir'] = $message;
                        }

                        continue;
                    }
                } else {
                    $status = false;
                    $message = "FAILED: $_config_dir does not exist";
                    if ($errors === null) {
                        echo $message . ".\n";
                    } else {
                        $errors['config_dir'] = $message;
                    }

                    continue;
                }
            }

            if (!is_dir($config_dir)) {
                $status = false;
                $message = "FAILED: $config_dir is not a directory";
                if ($errors === null) {
                    echo $message . ".\n";
                } else {
                    $errors['config_dir'] = $message;
                }
            } elseif (!is_readable($config_dir)) {
                $status = false;
                $message = "FAILED: $config_dir is not readable";
                if ($errors === null) {
                    echo $message . ".\n";
                } else {
                    $errors['config_dir'] = $message;
                }
            } else {
                if ($errors === null) {
                    echo "$config_dir is OK.\n";
                }
            }
        }

        if ($errors === null) {
            echo "Testing sysplugin files...\n";
        }
        // test if sysplugins are available
        $source = SMARTY_SYSPLUGINS_DIR;
        if (is_dir($source)) {
            $expected = array(
                "Smarty_Cache_Resource.php" => true,
                "Smarty_Cache_Resource_custom.php" => true,
                "Smarty_Cache_Resource_keyvaluestore.php" => true,
                "Smarty_Compiler_Cose.php" => true,
                "Smarty_Template_Class.php" => true,
                "Smarty_Cache_Resource_File.php" => true,
                "Smarty_Compiler_Template_Tag_append.php" => true,
                "Smarty_Compiler_Template_Tag_assign.php" => true,
                "Smarty_Compiler_Template_Tag_block.php" => true,
                "Smarty_Compiler_Template_Tag_break.php" => true,
                "Smarty_Compiler_Template_Tag_call.php" => true,
                "Smarty_Compiler_Template_Tag_capture.php" => true,
                "Smarty_Compiler_Template_Tag_ConfigLoad.php" => true,
                "Smarty_Compiler_Template_Tag_continue.php" => true,
                "Smarty_Compiler_Template_Tag_debug.php" => true,
                "Smarty_Compiler_Template_Tag_eval.php" => true,
                "Smarty_Compiler_Template_Tag_extends.php" => true,
                "Smarty_Compiler_Template_Tag_for.php" => true,
                "Smarty_Compiler_Template_Tag_foreach.php" => true,
                "Smarty_Compiler_Template_Tag_function.php" => true,
                "Smarty_Compiler_Template_Tag_if.php" => true,
                "Smarty_Compiler_Template_Tag_include.php" => true,
                "Smarty_Compiler_Template_Tag_include_php.php" => true,
                "Smarty_Compiler_Template_Tag_insert.php" => true,
                "Smarty_Compiler_Template_Tag_ldelim.php" => true,
                "Smarty_Compiler_Template_Tag_nocache.php" => true,
                "Smarty_Compiler_Template_Tag_Internal_PluginBlock.php" => true,
                "Smarty_Compiler_Template_Tag_Internal_PluginFunction.php" => true,
                "Smarty_Compiler_Template_Tag_Internal_modifier.php" => true,
                "Smarty_Compiler_Template_Tag_Internal_ObjectBlockFunction.php" => true,
                "Smarty_Compiler_Template_Tag_Internal_ObjectFunction.php" => true,
                "Smarty_Compiler_Template_Tag_Internal_PrintExpression.php" => true,
                "Smarty_Compiler_Template_Tag_Internal_RegisteredBlock.php" => true,
                "Smarty_Compiler_Template_Tag_Internal_RegisteredFunction.php" => true,
                "Smarty_Compiler_Template_Tag_Internal_SpecialVariable.php" => true,
                "Smarty_Compiler_Template_Tag_rdelim.php" => true,
                "Smarty_Compiler_Template_Tag_section.php" => true,
                "Smarty_Compiler_Template_Tag_setfilter.php" => true,
                "Smarty_Compiler_Template_Tag_while.php" => true,
                "$tpl_obj.php" => true,
                "Smarty_Internal_Config_Compiler.php" => true,
                "smarty_internal_configfilelexer.php" => true,
                "smarty_internal_configfileparser.php" => true,
                "Smarty_Variable_Methods.php" => true,
                "Smarty_Debug.php" => true,
                "Smarty_Misc_FilterHandler.php" => true,
                "Smarty_Misc_GetIncludePath.php" => true,
                "smarty_internal_nocache_insert.php" => true,
                "Smarty_Resource_Eval.php" => true,
                "Smarty_Resource_Extends.php" => true,
                "Smarty_Resource_File.php" => true,
                "Smarty_Resource_Registered.php" => true,
                "Smarty_Resource_Stream.php" => true,
                "Smarty_Resource_String.php" => true,
                "smarty_internal_smartytemplatecompiler.php" => true,
                "smarty_Internal_Template_.php" => true,
                "smarty_Internal_Template_base.php" => true,
                "Smarty_Compiler.php" => true,
                "smarty_Internal_Template_lexer.php" => true,
                "smarty_Internal_Template_parser.php" => true,
                "Smarty_Misc_Utility.php" => true,
                "Smarty_Misc_WriteFile.php" => true,
                "smarty_resource.php" => true,
                "smarty_resource_custom.php" => true,
                "smarty_resource_recompiled.php" => true,
                "smarty_resource_uncompiled.php" => true,
                "smarty_security.php" => true,
            );
            $iterator = new DirectoryIterator($source);
            foreach ($iterator as $file) {
                if (!$file->isDot()) {
                    $filename = $file->getFilename();
                    if (isset($expected[$filename])) {
                        unset($expected[$filename]);
                    }
                }
            }
            if ($expected) {
                $status = false;
                $message = "FAILED: files missing from libs/sysplugins: " . join(', ', array_keys($expected));
                if ($errors === null) {
                    echo $message . ".\n";
                } else {
                    $errors['sysplugins'] = $message;
                }
            } elseif ($errors === null) {
                echo "... OK\n";
            }
        } else {
            $status = false;
            $message = "FAILED: " . SMARTY_SYSPLUGINS_DIR . ' is not a directory';
            if ($errors === null) {
                echo $message . ".\n";
            } else {
                $errors['sysplugins_dir_constant'] = $message;
            }
        }

        if ($errors === null) {
            echo "Testing plugin files...\n";
        }
        // test if core plugins are available
        $source = SMARTY_PLUGINS_DIR;
        if (is_dir($source)) {
            $expected = array(
                "block.textformat.php" => true,
                "function.counter.php" => true,
                "function.cycle.php" => true,
                "function.fetch.php" => true,
                "function.html_checkboxes.php" => true,
                "function.html_image.php" => true,
                "function.html_options.php" => true,
                "function.html_radios.php" => true,
                "function.html_select_date.php" => true,
                "function.html_select_time.php" => true,
                "function.html_table.php" => true,
                "function.mailto.php" => true,
                "function.math.php" => true,
                "modifier.capitalize.php" => true,
                "modifier.date_format.php" => true,
                "modifier.debug_print_var.php" => true,
                "modifier.escape.php" => true,
                "modifier.regex_replace.php" => true,
                "modifier.replace.php" => true,
                "modifier.spacify.php" => true,
                "modifier.truncate.php" => true,
                "modifiercompiler.cat.php" => true,
                "modifiercompiler.count_characters.php" => true,
                "modifiercompiler.count_paragraphs.php" => true,
                "modifiercompiler.count_sentences.php" => true,
                "modifiercompiler.count_words.php" => true,
                "modifiercompiler.default.php" => true,
                "modifiercompiler.escape.php" => true,
                "modifiercompiler.from_charset.php" => true,
                "modifiercompiler.indent.php" => true,
                "modifiercompiler.lower.php" => true,
                "modifiercompiler.noprint.php" => true,
                "modifiercompiler.string_format.php" => true,
                "modifiercompiler.strip.php" => true,
                "modifiercompiler.strip_tags.php" => true,
                "modifiercompiler.to_charset.php" => true,
                "modifiercompiler.unescape.php" => true,
                "modifiercompiler.upper.php" => true,
                "modifiercompiler.wordwrap.php" => true,
                "outputfilter.trimwhitespace.php" => true,
                "shared.escape_special_chars.php" => true,
                "shared.literal_compiler_param.php" => true,
                "shared.make_timestamp.php" => true,
                "shared.mb_str_replace.php" => true,
                "shared.mb_unicode.php" => true,
                "shared.mb_wordwrap.php" => true,
                "variablefilter.htmlspecialchars.php" => true,
            );
            $iterator = new DirectoryIterator($source);
            foreach ($iterator as $file) {
                if (!$file->isDot()) {
                    $filename = $file->getFilename();
                    if (isset($expected[$filename])) {
                        unset($expected[$filename]);
                    }
                }
            }
            if ($expected) {
                $status = false;
                $message = "FAILED: files missing from libs/plugins: " . join(', ', array_keys($expected));
                if ($errors === null) {
                    echo $message . ".\n";
                } else {
                    $errors['plugins'] = $message;
                }
            } elseif ($errors === null) {
                echo "... OK\n";
            }
        } else {
            $status = false;
            $message = "FAILED: " . SMARTY_PLUGINS_DIR . ' is not a directory';
            if ($errors === null) {
                echo $message . ".\n";
            } else {
                $errors['plugins_dir_constant'] = $message;
            }
        }

        if ($errors === null) {
            echo "Tests complete.\n";
            echo "</PRE>\n";
        }

        return $status;
    }

}
