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
    public $class_name = '';

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
     * @return void
     */
    public function populate(Smarty $tpl_obj)
    {
        $this->filepath = $this->buildFilepath($tpl_obj);
        $this->timestamp = @filemtime($this->filepath);
        $this->exists = !!$this->timestamp;
    }

    /**
     * build cache file filepath
     *
     * @param  Smarty $tpl_obj template object
     * @return string filepath
     */
    public function buildFilepath(Smarty $tpl_obj = null)
    {
        $_source_file_path = str_replace(':', '.', $this->source->filepath);
        $_cache_id = isset($tpl_obj->cache_id) ? preg_replace('![^\w\|]+!', '_', $tpl_obj->cache_id) : null;
        $_compile_id = isset($tpl_obj->compile_id) ? preg_replace('![^\w\|]+!', '_', $tpl_obj->compile_id) : null;
        // if use_sub_dirs build subfolders
        if ($tpl_obj->use_sub_dirs) {
            $_filepath = substr($this->source->uid, 0, 2) . '/' . $this->source->uid . '/';
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
            $_filepath = str_replace('|', '.', $_cache_id) . '^' . $_compile_id . '^' . $this->source->uid . '.';
        }
        $_cache_dir = $tpl_obj->getCacheDir();
        if ($tpl_obj->cache_locking) {
            // create locking file name
            // relative file name?
            if (!preg_match('/^([\/\\\\]|[a-zA-Z]:[\/\\\\])/', $_cache_dir)) {
                $_lock_dir = rtrim(getcwd(), '/\\') . '/' . $_cache_dir;
            } else {
                $_lock_dir = $_cache_dir;
            }
            $this->lock_id = $_lock_dir . sha1($_cache_id . $_compile_id . $this->source->uid) . '.lock';
        }

        return $_cache_dir . $_filepath . basename($_source_file_path) . '.php';
    }

    /**
     * Instance compiled template
     *
     * @param Smarty $smarty     Smarty object
     * @param Smarty|Smarty_Data|Smarty_Template_Class $parent     parent object
     * @param  int $scope_type
     * @param  array $data             array with variable names and values which must be assigned
     * @param  bool $no_output_filter flag that output filter shall be ignored
     * @returns Smarty_Template_Class
     */
    function instanceTemplate($smarty, $parent, $scope_type, $data, $no_output_filter)
    {
        if ($this->class_name == '') {
            return $this->loadTemplate($smarty, $parent, $scope_type, $data, $no_output_filter);
        } else {
            return new $this->class_name($smarty, $parent, $this->source);
        }

    }


    /**
     * load compiled template class
     *     * @return void
     */
    public function loadTemplateClass()
    {
        if ($this->exists) {
            include $this->filepath;
        }
    }

    /**
     * Load compiled template
     *
     * @param Smarty $smarty     Smarty object
     * @param Smarty|Smarty_Data|Smarty_Template_Class $parent     parent object
     * @param  int $scope_type
     * @param  array $data             array with variable names and values which must be assigned
     * @param  bool $no_output_filter flag that output filter shall be ignored
     * @returns Smarty_Template_Class
     * @throws Smarty_Exception
     */
    public function loadTemplate($smarty, $parent, $scope_type, $data, $no_output_filter)
    {
        try {
            $level = ob_get_level();
            $isValid = false;
            if ($this->exists && !$smarty->force_compile && $this->timestamp >= $this->source->timestamp) {
                // load existing compiled template class
                $this->loadTemplateClass();
                if (class_exists($this->class_name, false)) {
                    $template_obj = new $this->class_name($smarty, $parent, $this->source);
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
                    $_output = $smarty->_load(Smarty::COMPILED, $this->source, $this->compile_id, $this->caching)->getRenderedTemplate($smarty, $parent, $scope_type, $data, $no_output_filter);
                }
                // write to cache when necessary
                if (!$this->source->recompiled) {
                    self::$creator[0]->_createCacheFile($this, $smarty, $_output, $no_output_filter);
                }
                unset($_output);
                array_shift(self::$creator);
                $this->loadTemplateClass($this);
                if (class_exists($this->class_name, false)) {
                    $template_obj = new $this->class_name($smarty, $parent, $this->source);
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
     * @param Smarty|Smarty_Data|Smarty_Template_Class $parent     parent object
     * @param  Smarty_Variable_Scope $_scope
     * @param  int $scope_type
     * @param  array $data             array with variable names and values which must be assigned
     * @param  bool $no_output_filter flag that output filter shall be ignored
     * @param  bool $display
     * @throws Exception
     * @return bool|string
     */
    public function getRenderedTemplate($smarty, $parent, $scope_type = Smarty::SCOPE_LOCAL, $data, $no_output_filter, $display)
    {
        $template_obj = $this->instanceTemplate($smarty, $parent, $scope_type, $data, $no_output_filter);
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
            $output = $template_obj->getRenderedTemplate($scope_type, $data, $no_output_filter);
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
            $tpl->usage = Smarty::IS_TEMPLATE;
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
     * populate Cached Object with timestamp and exists from Resource
     *
     * @return void
     */
    public function populateTimestamp(Smarty $tpl_obj)
    {
        $this->timestamp = @filemtime($this->filepath);
        $this->exists = !!$this->timestamp;
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
        if (Smarty_Misc_WriteFile::writeFile($this->filepath, $content, $tpl_obj) === true) {
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
