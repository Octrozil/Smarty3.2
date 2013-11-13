<?php

/**
 * Smarty Resource Cache File
 *
 * @package Resource\Cache
 * @author Rodney Rehm
 * @author Uwe Tews
 */

/**
 * This class does contain all necessary methods for the HTML cache on file system
 *
 * Implements the file system as resource for the HTML cache Version using nocache inserts.
 *
 * @package Resource\Cache
 */
class Smarty_Resource_Cache_File //extends Smarty_Exception_Magic
{

    /**
     * Id for cache locking
     * @var string
     */
    public $lock_id = null;

    /**
     * flag that cache is locked by this instance
     * @var bool
     */
    public $is_locked = false;

    /**
     * populate Compiled Object with compiled filepath
     *
     * @param  Smarty_Context $context
     * @return string
     */
    public function buildFilepath(Smarty_Context $context)
    {
        $_source_file_path = str_replace(':', '.', $context->filepath);
        $_cache_id = isset($context->cache_id) ? preg_replace('![^\w\|]+!', '_', $context->cache_id) : null;
        $_compile_id = isset($context->compile_id) ? preg_replace('![^\w\|]+!', '_', $context->compile_id) : null;
        // if use_sub_dirs build subfolders
        if ($context->smarty->use_sub_dirs) {
            $_filepath = substr($context->uid, 0, 2) . '/' . $context->uid . '/';
            if (isset($_cache_id)) {
                $_cache_id_parts = explode('|', $_cache_id);
                $_cache_id_last = count($_cache_id_parts) - 1;
                $_cache_id_hash = md5($_cache_id_parts[$_cache_id_last]);
                if ($_cache_id_last > 0) {
                    for ($i = 0; $i < $_cache_id_last; $i++) {
                        $_filepath .= $_cache_id_parts[$i] . '/';
                    }
                }
                $_filepath .= substr($_cache_id_hash, 0, 2) . '/'
                    . substr($_cache_id_hash, 2, 2) . '/'
                    . substr($_cache_id_hash, 4, 2) . '/';
                $_filepath .= $_cache_id_parts[$_cache_id_last];
            }
            $_filepath .= '^' . $_compile_id . '^';
        } else {
            $_filepath = str_replace('|', '.', $_cache_id) . '^' . $_compile_id . '^' . $context->uid . '.';
        }
        $_cache_dir = $context->smarty->getCacheDir();
        if ($context->smarty->cache_locking) {
            // create locking file name
            // relative file name?
            if (!preg_match('/^([\/\\\\]|[a-zA-Z]:[\/\\\\])/', $_cache_dir)) {
                $_lock_dir = rtrim(getcwd(), '/\\') . '/' . $_cache_dir;
            } else {
                $_lock_dir = $_cache_dir;
            }
            $this->lock_id = $_lock_dir . sha1($_cache_id . $_compile_id . $context->uid) . '.lock';
        }

        return $_cache_dir . $_filepath . basename($_source_file_path) . '.php';
    }

    /**
     * Load cached template
     *
     * @param Smarty_Context $context
     * @throws Exception
     * @returns Smarty_Template
     */
    public function instanceTemplate(Smarty_Context $context)
    {
        $timestamp = $exists = false;
        $filepath = $this->buildFilepath($context);
        $this->populateTimestamp($context->smarty, $filepath, $timestamp, $exists);
        try {
            $level = ob_get_level();
            $isValid = false;
            if ($exists && !$context->smarty->force_compile && !$context->smarty->force_cache && $timestamp >= $context->timestamp) {
                $template_class_name = '';
                // load existing compiled template class
                $template_class_name = $this->loadTemplateClass($filepath);
                if (class_exists($template_class_name, false)) {
                    $template_obj = new $template_class_name($context);
                    $isValid = $template_obj->isValid;
                }
            }
            if (!$isValid) {
                // rebuild cache file
                $obj = new Smarty_Resource_Cache_Extension_Create($this, $filepath);
                $obj->_renderCacheSubTemplate($context);
                unset($obj);
                // load existing compiled template class
                $this->populateTimestamp($context->smarty, $filepath, $timestamp, $exists);
                if ($exists) {
                    $template_class_name = '';
                    $template_class_name = $this->loadTemplateClass($filepath);
                    if (class_exists($template_class_name, false)) {
                        $template_obj = new $template_class_name($context);
                        $template_obj->isUpdated = true;
                        $isValid = $template_obj->isValid;
                        if ($context->smarty->enable_trace && isset(Smarty::$_trace_callbacks['cache:update'])) {
                            $context->smarty->_triggerTraceCallback('cache:update', array($template_obj));
                        }
                    }
                }
                if (!$isValid) {
                    throw new Smarty_Exception("Unable to load cached template file '{$filepath}'");
                }
            }
        } catch (Exception $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
//            throw new Smarty_Exception_Runtime('resource ', -1, null, null, $e);
            throw $e;
        }
        return $template_obj;
    }


    /**
     * Check timestamp of browser cache against timestamp of individually cached subtemplates
     *
     * @param  Smarty $smarty                  template object
     * @param  integer $_last_modified_timestamp browser cache timestamp
     * @return bool    true if browser cache is valid
     */
    private function checkSubtemplateCache($smarty, $_last_modified_timestamp)
    {
        $subtpl = reset($smarty->cached_subtemplates);
        while ($subtpl) {
            $tpl = clone $this;
            unset($tpl->source, $tpl->compiled, $tpl->cached, $tpl->compiler, $tpl->mustCompile);
            $tpl->_usage = Smarty::IS_SMARTY_TPL_CLONE;
            $tpl->template_resource = $subtpl[0];
            $tpl->cache_id = $subtpl[1];
            $tpl->compile_id = $subtpl[2];
            $tpl->caching = $subtpl[3];
            $tpl->cache_lifetime = $subtpl[4];
            if (!$tpl->cached->valid || $tpl->has_nocache_code || $tpl->cached->timestamp > $_last_modified_timestamp ||
                !$this->checkSubtemplateCache($tpl, $_last_modified_timestamp)
            ) {
                // browser cache invalid
                return false;
            }
            $subtpl = next($smarty->cached_subtemplates);
        }
        // browser cache valid
        return true;
    }

    /**
     * load cache template class
     *
     * @param $filepath
     * @return string  template class name
     */
    public function loadTemplateClass($filepath)
    {
        $template_class_name = '';
        include $filepath;
        return $template_class_name;
    }

    /**
     * get timestamp and exists from Resource
     *
     * @param  Smarty $smarty     Smarty object
     * @param $filepath
     * @param $timestamp
     * @param $exists
     * @return boolean  true if file exits
     */
    public function populateTimestamp(Smarty $smarty, $filepath, &$timestamp, &$exists)
    {
        if (is_file($filepath)) {
            $timestamp = filemtime($filepath);
            $exists = true;
        } else {
            $timestamp = $exists = false;
        }
    }

    /**
     * Write the rendered template output to cache
     *
     * @param  Smarty $tpl_obj template object
     * @param  string $filepath filepath
     * @param  string $content content to cache
     * @return boolean success
     */
    public function writeCache(Smarty $tpl_obj, $filepath, $content)
    {
        return $tpl_obj->writeFile($filepath, $content);
    }

    /**
     * Empty cache
     *
     * @param  Smarty $smarty   Smarty object
     * @param  integer $exp_time expiration time (number of seconds, not timestamp)
     * @return integer number of cache files deleted
     */
    public function clearAll(Smarty $smarty, $exp_time = null)
    {
        $save_use_sub_dirs = $smarty->use_sub_dirs;
        $smarty->use_sub_dirs = false;
        $count = $this->clear($smarty, null, null, null, $exp_time);
        $smarty->use_sub_dirs = true;
        $count += $this->clear($smarty, null, null, null, $exp_time);
        $smarty->use_sub_dirs = $save_use_sub_dirs;
        return $count;
    }

    /**
     * Empty cache for a specific template
     *
     * @param  Smarty $smarty        Smarty object
     * @param  string $resource_name template name
     * @param  string $cache_id      cache id
     * @param  string $compile_id    compile id
     * @param  integer $exp_time      expiration time (number of seconds, not timestamp)
     * @return integer number of cache files deleted
     */
    public function clear(Smarty $smarty, $resource_name, $cache_id, $compile_id, $exp_time)
    {
        // is external to save memory
        return Smarty_Resource_Cache_Extension_File::clear($smarty, $resource_name, $cache_id, $compile_id, $exp_time);
    }

    /**
     * Check is cache is locked for this template
     *
     * @param  Smarty $smarty Smarty object
     * @return bool   true or false if cache is locked
     */
    public function hasLock(Smarty $smarty)
    {
        if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
            clearstatcache(true, $this->lock_id);
        } else {
            clearstatcache();
        }
        $t = @filemtime($this->lock_id);

        return $t && (time() - $t < $smarty->locking_timeout);
    }

    /**
     * Lock cache for this template
     *
     * @param  Smarty $smarty Smarty object
     * @return void
     */
    public function acquireLock(Smarty $smarty)
    {
        $this->is_locked = true;
        touch($this->lock_id);
    }

    /**
     * Unlock cache for this template
     *
     * @param  Smarty $smarty Smarty object
     * @return void
     */
    public function releaseLock(Smarty $smarty)
    {
        $this->is_locked = false;
        @unlink($this->lock_id);
    }

}
