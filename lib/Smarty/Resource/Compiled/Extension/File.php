<?php

/**
 * Smarty Resource Compiled File Plugin
 *
 *
 * @package Resource\Compiled
 * @author Uwe Tews
 */

/**
 * Smarty Resource Compiled File Extension
 *
 *
 *
 */
class Smarty_Resource_Compiled_Extension_File
{

    /**
     * Delete compiled template file
     *
     * @param  Smarty $smarty            Smarty instance
     * @param  string $template_resource template name
     * @param  string $compile_id        compile id
     * @param  integer $exp_time          expiration time
     * @param  boolean $isConfig         true if a config file
     * @return integer number of template files deleted
     */
    public static function clear(Smarty $smarty, $template_resource, $compile_id, $exp_time, $isConfig)
    {
        $_compile_dir = str_replace('\\', '/', $smarty->getCompileDir());
        $_compile_id = isset($compile_id) ? preg_replace('![^\w\|]+!', '_', $compile_id) : null;
        $compiletime_options = 0;
        $_dir_sep = $smarty->use_sub_dirs ? '/' : '^';
        if (isset($template_resource)) {
            $source = $smarty->_getSourceObject($template_resource, $isConfig);
           if ($source->exists) {
                // set basename if not specified
                $_basename = $source->getBasename();
                if ($_basename === null) {
                    $_basename = basename(preg_replace('![^\w\/]+!', '_', $source->name));
                }
                // separate (optional) basename by dot
                if ($_basename) {
                    $_basename = '.' . $_basename;
                }
                $_resource_part_1 = $source->uid . '_' . $compiletime_options . '.' . $source->type . $_basename . '.php';
                $_resource_part_1_length = strlen($_resource_part_1);
            } else {
                return 0;
            }

            $_resource_part_2 = str_replace('.php', '.cache.php', $_resource_part_1);
            $_resource_part_2_length = strlen($_resource_part_2);
        }
        $_dir = $_compile_dir;
        if ($smarty->use_sub_dirs && isset($_compile_id)) {
            $_dir .= $_compile_id . $_dir_sep;
        }
        if (isset($_compile_id)) {
            $_compile_id_part = $_compile_dir . $_compile_id . $_dir_sep;
        }
        $_count = 0;
        try {
            $_compileDirs = new RecursiveDirectoryIterator($_dir);
            // NOTE: UnexpectedValueException thrown for PHP >= 5.3
        } catch (Exception $e) {
            return 0;
        }
        $_compile = new RecursiveIteratorIterator($_compileDirs, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($_compile as $_file) {
            if (substr($_file->getBasename(), 0, 1) == '.' || strpos($_file, '.svn') !== false)
                continue;

            $_filepath =  str_replace('\\', '/', (string)$_file);

            if ($_file->isDir()) {
                if (!$_compile->isDot()) {
                    // delete folder if empty
                    @rmdir($_file->getPathname());
                }
            } else {
                $unlink = false;
                if ((!isset($_compile_id) || strpos($_filepath, $_compile_id_part) === 0)
                    && (!isset($template_resource)
                        || (isset($_filepath[$_resource_part_1_length])
                            && substr_compare($_filepath, $_resource_part_1, -$_resource_part_1_length, $_resource_part_1_length) == 0)
                        || (isset($_filepath[$_resource_part_2_length])
                            && substr_compare($_filepath, $_resource_part_2, -$_resource_part_2_length, $_resource_part_2_length) == 0))
                ) {
                    if (isset($exp_time)) {
                        if (time() - @filemtime($_filepath) >= $exp_time) {
                            $unlink = true;
                        }
                    } else {
                        $unlink = true;
                    }
                }

                if ($unlink && @unlink($_filepath)) {
                    $_count++;
                    if ($smarty->enable_trace) {
                        // notify listeners of deleted file
                        $smarty->_triggerTraceCallback('filesystem:delete', array($smarty, $path));
                    }
                }
            }
        }
       return $_count;
    }
}
