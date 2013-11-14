<?php

/**
 * Smarty Resource Cache File Plugin
 *
 *
 * @package Smarty\Resource\Cache
 * @author Uwe Tews
 */

/**
 * Smarty Resource Cache File Extension
 *
 *
 *
 */
class Smarty_Resource_Cache_Extension_File
{

    /**
     * Delete cache file for a specific template
     *
     * @internal
     * @param  Smarty $smarty        Smarty object
     * @param  string $resource_name template name
     * @param  string $cache_id      cache id
     * @param  string $compile_id    compile id
     * @param  integer $exp_time      expiration time (number of seconds, not timestamp)
     * @return integer number of cache files deleted
     */
    public static function clear(Smarty $smarty, $resource_name, $cache_id, $compile_id, $exp_time)
    {
        $_cache_id = isset($cache_id) ? preg_replace('![^\w\|]+!', '_', $cache_id) : null;
        $_compile_id = isset($compile_id) ? preg_replace('![^\w\|]+!', '_', $compile_id) : null;
        $_preg_compile_id = ($_compile_id == null) ? '(.*)?' : preg_quote($_compile_id);
        $_preg_cache_id = '(.*)?';
        $_preg_file = '(.*)?';
        $_cache_dir = str_replace('\\', '/', $smarty->getCacheDir());
        $_count = 0;
        $_time = time();

        if (isset($resource_name)) {
            $context = $smarty->_getContext($resource_name);
            if ($context->exists) {
                // set basename if not specified
                $_basename = $context->getBasename($context);
                if ($_basename === null) {
                    $_basename = basename(preg_replace('![^\w\/]+!', '_', $context->name));
                }
                // separate (optional) basename by dot
                //                if ($_basename) {
                //                    $_basename = '.' . $_basename;
                //                }
                if ($smarty->use_sub_dirs) {
                    $_preg_file = preg_quote($_basename);
                    $_dirtpl_obj = $_cache_dir . substr($context->uid, 0, 2) . '/' . $context->uid . '/';
                    // does subdir for template exits?
                    if (!is_dir($_dirtpl_obj)) {
                        return 0;
                    }
                    // use template subdir as top level
                    $_dir_array = array($_dirtpl_obj);
                } else {
                    $_preg_file = preg_quote($context->uid . '.' . $_basename);
                }
            } else {
                // template does not exist
                return 0;
            }
        }
        // if use_sub_dirs iterate over folder
        if ($smarty->use_sub_dirs) {
            // if no template was specified build top level array for all templates
            if (!isset($resource_name)) {
                $_dir_array = array();
                $_dir_it1 = new DirectoryIterator($_cache_dir);
                foreach ($_dir_it1 as $_dir1) {
                    if (!$_dir1->isDir() || $_dir1->isDot() || substr(basename($_dir1->getPathname()), 0, 1) == '.') {
                        continue;
                    }
                    $_dir_it2 = new DirectoryIterator($_dir1->getPathname());
                    foreach ($_dir_it2 as $_dir2) {
                        if (!$_dir2->isDir() || $_dir2->isDot() || substr(basename($_dir2->getPathname()), 0, 1) == '.') {
                            continue;
                        }
                        $_dir_array[] = $_dir2->getPathname() . '/';
                    }
                }
            }
            $_dir_cache_id = '';
            // build subfolders by cache_id
            if (isset($_cache_id)) {
                $_cache_id_parts = explode('|', $_cache_id);
                $_cache_id_last = count($_cache_id_parts) - 1;
                $_cache_id_hash = md5($_cache_id_parts[$_cache_id_last]);
                // lower levels of structured cache_id
                if ($_cache_id_last > 0) {
                    for ($i = 0; $i < $_cache_id_last; $i++) {
                        $_dir_cache_id .= $_cache_id_parts[$i] . '/';
                    }
                }
                // hash for highest level of cache_id
                $_dir_cache_id2 = $_dir_cache_id . substr($_cache_id_hash, 0, 2) . '/'
                    . substr($_cache_id_hash, 2, 2) . '/'
                    . substr($_cache_id_hash, 4, 2) . '/';
                $_preg_cache_id2 = preg_quote($_cache_id_parts[$_cache_id_last]);
                // add highest level
                $_dir_cache_id .= $_cache_id_parts[$_cache_id_last] . '/';
            }
            // loop over templates
            foreach ($_dir_array as $dir) {
                $_dirs = array($dir . $_dir_cache_id, isset($_cache_id) ? $dir . $_dir_cache_id2 : null);
                $_deleted = array(false, false);
                for ($i = 0; $i < 2; $i++) {
                    if ($i == 0) {
                        if (!is_dir($_dirs[$i])) {
                            continue;
                        }
                        // folder for lower levels is present or no cache_id specified
                        $_cacheDirs1 = new RecursiveDirectoryIterator($_dirs[$i]);
                        $_cacheDirs = new RecursiveIteratorIterator($_cacheDirs1, RecursiveIteratorIterator::CHILD_FIRST);
                        $_preg_cache_id = '(.*)?';
                    } elseif (isset($_cache_id)) {
                        if (!is_dir($_dirs[$i])) {
                            continue;
                        }
                        // folder with highest level hash is present
                        $_cacheDirs = new DirectoryIterator($_dirs[$i]);
                        $_preg_cache_id = $_preg_cache_id2;
                    }
                    if ($i == 0 || isset($_cache_id)) {
                        $regex = "/^{$_preg_cache_id}\^{$_preg_compile_id}\^{$_preg_file}\.php\$/i";
                        foreach ($_cacheDirs as $_file) {
                            // directory ?
                            if ($_file->isDir()) {
                                if (!$_cacheDirs->isDot()) {
                                    // delete folder if empty
                                    @rmdir($_file->getPathname());
                                    continue;
                                }
                            }
                            $path = $_file->getPathname();
                            if (substr(basename($path), 0, 1) == '.') {
                                continue;
                            }
                            $filename = str_replace('\\', '/', $path);
                            // does file match selections?
                            if (!preg_match($regex, $filename, $matches)) {
                                continue;
                            }
                            // expired ?
                            if (isset($exp_time) && $_time - @filemtime($path) < $exp_time) {
                                continue;
                            }
                            $_count += @unlink($path) ? 1 : 0;
                            $_deleted[$i] = true;
                            if ($smarty->enable_trace && isset(Smarty::$_trace_callbacks['cache:delete'])) {
                                $smarty->_triggerTraceCallback('cache:delete', array($path, $compile_id, $cache_id, $exp_time));
                            }
                        }
                    }
                    unset($_cacheDirs, $_cacheDirs1);
                    if ($_deleted[$i]) {
                        $_dir = $_dirs[$i];
                        while ($_dir != $_cache_dir) {
                            if (@rmdir($_dir) === false) {
                                break;
                            }
                            $_dir = substr($_dir, 0, strrpos(substr($_dir, 0, -1), '/') + 1);
                        }
                    }
                }
            }
        } else {
            if (isset($_cache_id)) {
                $_preg_cache_id = preg_quote(str_replace('|', '.', $_cache_id)) . '(?=[\^\.])(.*)?';
            }
            $regex = "/^{$_preg_cache_id}\^{$_preg_compile_id}\^{$_preg_file}\.php\$/i";
            $_cacheDirs = new DirectoryIterator($_cache_dir);
            foreach ($_cacheDirs as $_file) {
                // directory ?
                if ($_file->isDir()) {
                    continue;
                }
                $path = $_file->getPathname();
                $filename = basename($path);
                // does file match selections?
                if (!preg_match($regex, $filename, $matches)) {
                    continue;
                }
                // expired ?
                if (isset($exp_time)) {
                    if ($exp_time < 0) {
                        preg_match('#$cache_lifetime =\s*(\d*)#', file_get_contents($path), $match);
                        if ($_time < (@filemtime($path) + $match[1])) {
                            continue;
                        }
                    } else {
                        if ($_time - @filemtime($path) < $exp_time) {
                            continue;
                        }
                    }
                }
                $_count += @unlink($path) ? 1 : 0;
                if ($smarty->enable_trace && isset(Smarty::$_trace_callbacks['cache:delete'])) {
                    $smarty->_triggerTraceCallback('cache:delete', array($path, $compile_id, $cache_id, $exp_time));
                }
            }
        }

        return $_count;
    }
}
