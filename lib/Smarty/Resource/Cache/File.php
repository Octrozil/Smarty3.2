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
class Smarty_Resource_Cache_File extends Smarty_Exception_Magic
{

    /**
     * resource filepath
     *
     * @var string| boolean false
     */
    public $filepath = false;

    /**
     * Resource Timestamp
     * @var integer
     */
    public $timestamp = null;

    /**
     * Resource Existence
     * @var boolean
     */
    public $exists = false;

    /**
     * Cache Is Valid
     * @var boolean
     */
    public $isValid = false;

    /**
     * Template Compile Id (Smarty::$compile_id)
     * @var string
     */
    public $compile_id = null;

    /**
     * Template Cache Id (Smarty::$cache_id)
     * @var string
     */
    public $cache_id = null;

    /**
     * Flag if caching enabled
     * @var boolean
     */
    public $caching = false;

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
     * Source Object
     * @var Smarty_Template_Source
     */
    public $source = null;

    /**
     * Template Class Name
     * @var string
     */
    public $template_class_name = '';

    /**
     * Template Class Object
     * @var object
     */
    public $template_obj = null;

    /**
     * Handler for updating cache files
     * @var array Smarty_Cache_Helper_Create
     */
    public static $creator = array();

    /**
     * populate Cached Object with meta data from Resource
     *
     * @param  Smarty $tpl_obj template object
     * @return boolean  true if cache file does exist
     */
    public function populate(Smarty $tpl_obj)
    {
        $this->filepath = $this->buildFilepath($tpl_obj);
        if (is_file($this->filepath)){
            $this->timestamp = filemtime($this->filepath);
            return $this->exists = true;
        }
        return $this->timestamp = $this->exists = false;
    }

    /**
     * build cache file filepath
     *
     * @param $smarty
     * @param $source
     * @param $compile_id
     * @param $cache_id
     * @return string filepath
     */
    public function buildFilepath($smarty, $source, $compile_id, $cache_id)
    {
        $_source_file_path = str_replace(':', '.', $source->filepath);
        $_cache_id = isset($cache_id) ? preg_replace('![^\w\|]+!', '_', $cache_id) : null;
        $_compile_id = isset($compile_id) ? preg_replace('![^\w\|]+!', '_', $compile_id) : null;
        // if use_sub_dirs build subfolders
        if ($smarty->use_sub_dirs) {
            $_filepath = substr($source->uid, 0, 2) . '/' . $source->uid . '/';
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
            $_filepath = str_replace('|', '.', $_cache_id) . '^' . $_compile_id . '^' . $source->uid . '.';
        }
        $_cache_dir = $smarty->getCacheDir();
        if ($smarty->cache_locking) {
            // create locking file name
            // relative file name?
            if (!preg_match('/^([\/\\\\]|[a-zA-Z]:[\/\\\\])/', $_cache_dir)) {
                $_lock_dir = rtrim(getcwd(), '/\\') . '/' . $_cache_dir;
            } else {
                $_lock_dir = $_cache_dir;
            }
            $this->lock_id = $_lock_dir . sha1($_cache_id . $_compile_id . $source->uid) . '.lock';
        }

        return $_cache_dir . $_filepath . basename($_source_file_path) . '.php';
    }

    /**
     * Instance compiled template
     *
     * @param Smarty $smarty     Smarty object
     * @param Smarty_Source $source
     * @param  mixed $compile_id       compile id to be used with this template
     * @param  mixed $cache_id         cache id to be used with this template
     * @param integer $caching
     * @param Smarty|Smarty_Data|Smarty_Template $parent     parent object
     * @param  null| Smarty_Variable_Scope $_scope
     * @param $scope_type
     * @param  bool $no_output_filter if true do not run output filter
     * @throws Exception
     * @returns Smarty_Template
     */
    function instanceTemplate($smarty, $source, $compile_id, $cache_id, $caching, $parent, $scope, $scope_type, $no_output_filter)
    {
        $this->source = $source;
        $this->compile_id = $compile_id;
        $this->cache_id = $cache_id;
        $this->caching = $caching;
        $this->timestamp = $this->exists = false;
        $this->filepath = $this->buildFilepath($smarty, $source, $compile_id, $cache_id);
        $this->populateTimestamp($smarty, $this->filepath, $this->timestamp, $this->exists);

        try {
            $level = ob_get_level();
            $isValid = false;
            if ($this->exists && !$smarty->force_compile && !$smarty->force_cache && $this->timestamp >= $this->source->timestamp) {
                $template_class_name = '';
                // load existing compiled template class
                $template_class_name = $this->loadTemplateClass($this->filepath);
                if (class_exists($template_class_name, false)) {
                    $template_obj = new $template_class_name($smarty, $source, $this->filepath, $this->timestamp);
                    $isValid = $template_obj->isValid;
                }
            }
            if (!$isValid) {
                // unshift new handler for cache creation in first position
                // cache could be nested as subtemplates can have individual cache
                array_unshift(self::$creator, new Smarty_Resource_Cache_Extension_Create());
                if ($source->uncompiled) {
                    $_output = $source->getRenderedTemplate($smarty, $scope, $scope_type, $data);
                } else {
                    $_output =  $source->_getRenderedTemplate($smarty, Smarty::COMPILED, $parent, $compile_id, $cache_id, $caching, $scope, $scope_type, $no_output_filter);
                }
                // write to cache when necessary
                if (!$this->source->recompiled) {
                    self::$creator[0]->_createCacheFile($this, $smarty, $_output, $no_output_filter);
                }
                unset($_output);
                array_shift(self::$creator);
                $template_class_name = '';
                // load existing compiled template class
                $template_class_name = $this->loadTemplateClass($this->filepath);
                if (class_exists($template_class_name, false)) {
                    $template_obj = new $template_class_name($smarty, $source,$this->filepath,$this->timestamp);
                    $template_obj->isUpdated = true;
                    $isValid = $template_obj->isValid;
                }
                if (!$isValid) {
                    throw new Smarty_Exception("Unable to load compiled template file '{$filepath}'");
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
     * Load compiled template
     *
     * @param Smarty $smarty     Smarty object
     * @param Smarty|Smarty_Data|Smarty_Template $parent     parent object
     * @param  null| Smarty_Variable_Scope $scope
     * @param  int $scope_type
     * @param  bool $no_output_filter flag that output filter shall be ignored
     * @throws Exception
     * @return
     * @returns Smarty_Template
     */
    public function loadTemplate($smarty, $parent, $scope, $scope_type, $no_output_filter)
    {
        try {
            $level = ob_get_level();
            $isValid = false;
            if ($this->exists && !$smarty->force_compile && $this->timestamp >= $this->source->timestamp) {
                // load existing compiled template class
                $this->loadTemplateClass();
                if (class_exists($template_class_name, false)) {
                    $template_obj = new $template_class_name($smarty, $this->source);
                    // existing class could got invalid
                    $isValid = $template_obj->isValid;
                }
            }
            if (!$isValid) {
                unset($template_obj);
                // unshift new handler for cache creation in first position
                // cache could be nested as subtemplates can have individual cache
                array_unshift(self::$creator, new Smarty_Resource_Cache_Extension_Create());
                if ($this->source->uncompiled) {
                    $_output = $this->source->getRenderedTemplate($smarty, $_scope, $scope_type, $data);
                } else {
                    $_output = $smarty->_getRenderedTemplate(Smarty::COMPILED, $this->source, $parent, $this->compile_id, null, $this->caching, $scope, $scope_type, $no_output_filter);
                }
                // write to cache when necessary
                if (!$this->source->recompiled) {
                    self::$creator[0]->_createCacheFile($this, $smarty, $_output, $no_output_filter);
                }
                unset($_output);
                array_shift(self::$creator);
                $this->loadTemplateClass($this);
                if (class_exists($template_class_name, false)) {
                    $template_obj = new $template_class_name($smarty, $this->source);
                    $isValid = $template_obj->isValid;
                }
                if (!$isValid) {
                    throw new Smarty_Exception("Unable to load compiled template file '{$this->filepath}'");
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
     * get rendered template output from cached template
     *
     * @param  Smarty $smarty          template object
     * @param Smarty|Smarty_Data|Smarty_Template $parent     parent object
     * @param  \Smarty_Variable_Scope $scope
     * @param int $scope_type
     * @param  bool $no_output_filter flag that output filter shall be ignored
     * @param  bool $display
     * @return bool|string
     */
    public function getRenderedTemplate($smarty, $parent, $scope, $scope_type = Smarty::SCOPE_LOCAL, $no_output_filter, $display)
    {
        $template_obj = $this->instanceTemplate($smarty, $parent, $scope, $scope_type, $no_output_filter);
        $browser_cache_valid = false;
        if ($display && $smarty->cache_modified_check && $this->isValid && !$template_obj->has_nocache_code) {
            $_last_modified_date = @substr($_SERVER['HTTP_IF_MODIFIED_SINCE'], 0, strpos($_SERVER['HTTP_IF_MODIFIED_SINCE'], 'GMT') + 3);
            if ($_last_modified_date !== false && $this->timestamp <= ($_last_modified_timestamp = strtotime($_last_modified_date)) &&
                $this->checkSubtemplateCache($smarty, $_last_modified_timestamp)
            ) {
                $browser_cache_valid = true;
                switch (PHP_SAPI) {
                    case 'cgi': // php-cgi < 5.3
                    case 'cgi-fcgi': // php-cgi >= 5.3
                    case 'fpm-fcgi': // php-fpm >= 5.3.3
                        header('Status: 304 Not Modified');
                        break;

                    case 'cli':
                        if ( /* ^phpunit */
                        !empty($_SERVER['SMARTY_PHPUNIT_DISABLE_HEADERS']) /* phpunit$ */
                        ) {
                            $_SERVER['SMARTY_PHPUNIT_HEADERS'][] = '304 Not Modified';
                        }
                        break;

                    default:
                        header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified');
                        break;
                }
            }
        }
        if (!$browser_cache_valid) {
            $output = $template_obj->getRenderedTemplate($parent, $scope, $scope_type, $no_output_filter);
            $smarty->is_nocache = false;
            if ($template_obj->has_nocache_code && !$no_output_filter && (isset($smarty->autoload_filters['output']) || isset($smarty->registered_filters['output']))) {
                $output = $smarty->runFilter('output', $output);
            }
            return $output;
        } else {
            // browser cache was valid
            return true;
        }
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
     * @param  string $content content to cache
     * @return boolean success
     */
    public function writeCache(Smarty $tpl_obj, $content)
    {
        if ($tpl_obj->writeFile($this->filepath, $content) === true) {
            $this->timestamp = @filemtime($this->filepath);
            $this->exists = !!$this->timestamp;
            if ($this->exists) {
                return true;
            }
        }

        return false;
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
