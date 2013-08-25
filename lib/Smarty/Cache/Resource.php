<?php

/**
 * Smarty Internal Plugin
 *
 *
 * @package Cacher
 */

/**
 * Cache Handler API
 *
 *
 * @package Cacher
 * @author Rodney Rehm
 */

abstract class Smarty_Cache_Resource extends Smarty_Exception_Magic
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
     * Handler for updating cache files
     * @var array Smarty_Cache_Helper_Create
     */
    public static $creator = array();

    /**
     * Populate cached resource properties
     *
     * @param Smarty $tpl_obj template object
     * @params Smarty_Resource $source source resource
     * @params mixed $compile_id  compile id
     * @params mixed $cache_id  cache id
     * @params boolean $caching caching enabled ?
     * @return Smarty_Compiled_Resource
     */
    public function populateResource($tpl_obj, $source, $compile_id, $cache_id, $caching)
    {
        $this->source = $source;
        $this->compile_id = $compile_id;
        $this->cache_id = $cache_id;
        $this->caching = $caching;
        $this->populate($tpl_obj);

        return $this;
    }

    /**
     * Read the cached template and process header
     *
     * @param  Smarty $tpl_obj template object
     * @return boolean true or false if the cached content does not exist
     */
    abstract public function process(Smarty $tpl_obj);

    /**
     * Write the rendered template output to cache
     *
     * @param  Smarty $tpl_obj template object
     * @param  string $content content to cache
     * @return boolean success
     */
    abstract public function writeCachedContent(Smarty $tpl_obj, $content);

    /**
     * Empty cache
     *
     * @param  Smarty $smarty   Smarty object
     * @param  integer $exp_time expiration time (number of seconds, not timestamp)
     * @return integer number of cache files deleted
     */
    abstract public function clearAll(Smarty $smarty, $exp_time = null);

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
    abstract public function clear(Smarty $smarty, $resource_name, $cache_id, $compile_id, $exp_time);

    public function locked(Smarty $tpl_obj)
    {
        // theoretically locking_timeout should be checked against time_limit (max_execution_time)
        $start = microtime(true);
        $hadLock = null;
        while ($this->hasLock($tpl_obj)) {
            $hadLock = true;
            if (microtime(true) - $start > $tpl_obj->locking_timeout) {
                // abort waiting for lock release
                return false;
            }
            sleep(1);
        }

        return $hadLock;
    }

    public function hasLock(Smarty $tpl_obj)
    {
        // check if lock exists
        return false;
    }

    public function acquireLock(Smarty $tpl_obj)
    {
        // create lock
        return true;
    }

    public function releaseLock(Smarty $tpl_obj)
    {
        // release lock
        return true;
    }

    /**
     * Invalid Loaded Cache Files
     *
     * @param Smarty $smarty Smarty object
     */
    public static function invalidLoadedCache(Smarty $smarty)
    {
        foreach (Smarty::$resource_cache as $source_key => $foo) {
            unset(Smarty::$resource_cache[$source_key]['cache']);
        }
    }

    /**
     * Empty cache for a specific template
     *
     * @internal
     * @param  string $template_name template name
     * @param  string $cache_id      cache id
     * @param  string $compile_id    compile id
     * @param  integer $exp_time      expiration time
     * @param  string $type          resource type
     * @param  Smarty $smarty        Smarty object
     * @return integer number of cache files deleted
     */
    public static function clearCache($template_name, $cache_id, $compile_id, $exp_time, $type, $smarty)
    {
        // load cache resource and call clear
        $_cache_resource = $smarty->_loadResource(SMARTY::CACHE, $type ? $type : $smarty->caching_type);
        Smarty_Cache_Resource::invalidLoadedCache($smarty);

        return $_cache_resource->clear($smarty, $template_name, $cache_id, $compile_id, $exp_time);
    }

    /**
     * Empty cache folder
     *
     * @api
     * @param  Smarty $smarty   Smarty object
     * @param  integer $exp_time expiration time
     * @param  string $type     resource type
     * @return integer number of cache files deleted
     */
    public static function clearAllCache(Smarty $smarty, $exp_time = null, $type = null)
    {
        $_cache_resource = $smarty->_loadResource(SMARTY::CACHE, $type ? $type : $smarty->caching_type);
        Smarty_Cache_Resource::invalidLoadedCache($smarty);

        return $_cache_resource->clearAll($smarty, $exp_time);
    }

    /**
     * Load compiled template
     *
     * @param  Smarty $tpl_obj template object
     * @param Smarty|Smarty_Data|Smarty_Template_Class $parent parent object
     * @params boolean $isCacheCheck true to just check if cache is valid
     * @return mixed Smarty_Template|false
     * @throws Smarty_Exception
     */
    public function loadTemplate($tpl_obj, $parent, $isCacheCheck)
    {
        if ($isCacheCheck && (!$this->exists || !$this->caching || $tpl_obj->force_compile || $tpl_obj->force_cache || $this->source->recompiled)) {
            return false;
        }
        try {
            $level = ob_get_level();
            $isValid = false;
            if ($this->exists && !$tpl_obj->force_compile && !$tpl_obj->force_cache) {
                $this->process($tpl_obj);
                $template_obj = new $this->class_name($tpl_obj, $parent, $this->source);
                $class_name = $this->class_name;
                $isValid = $class_name::$isValid;
            }
            if ($isCacheCheck) {
                return $isValid ? $template_obj : false;
            }
            if (!$isValid) {
                if ($tpl_obj->debugging) {
                    Smarty_Debug::start_compile($this->source);
                }
                $compiler = Smarty_Compiler::load($tpl_obj, $this->source, $this->caching);
                $compiler->compileTemplateSource($this);
                unset($compiler);
                if ($tpl_obj->debugging) {
                    Smarty_Debug::end_compile($this->source);
                }
                $this->process($tpl_obj);
                $template_obj = new $this->class_name($tpl_obj, $parent, $this->source);
                $class_name = $this->class_name;
                $isValid = $class_name::$isValid;
                if (!$isValid) {
                    throw new Smarty_Exception("Unable to load compiled template file '{$this->filepath}");
                }
            }
        } catch (Exception $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
            throw new Smarty_Exception_Runtime('resource ', -1, null, $e);
        }

    }

    /**
     * get rendered template output from cached template
     *
     * @param  Smarty $tpl_obj          template object
     * @param  Smarty_Variable_Scope $_scope
     * @param  int $scope_type
     * @param  array $data             array with variable names and values which must be assigned
     * @param  bool $no_output_filter flag that output filter shall be ignored
     * @param  bool $display
     * @throws Exception
     * @return bool|string
     */
    public function getRenderedTemplate($tpl_obj, $_scope, $scope_type, $data, $no_output_filter, $display)
    {
        $_scope = $tpl_obj->_buildScope($_scope, $scope_type, $data);
        $browser_cache_valid = false;
        if ($display && $tpl_obj->cache_modified_check && $this->isValid && !$this->template_obj->has_nocache_code) {
            $_last_modified_date = @substr($_SERVER['HTTP_IF_MODIFIED_SINCE'], 0, strpos($_SERVER['HTTP_IF_MODIFIED_SINCE'], 'GMT') + 3);
            if ($_last_modified_date !== false && $this->timestamp <= ($_last_modified_timestamp = strtotime($_last_modified_date)) &&
                $this->checkSubtemplateCache($tpl_obj, $_last_modified_timestamp)
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
            if (!$this->isValid) {
                // unshift new handler for cache creation in first position
                // cache could be nested as subtemplates can have individual cache
                array_unshift(self::$creator, new Smarty_Cache_Helper_Create());
                if ($this->source->uncompiled) {
                    $output = $this->source->getRenderedTemplate($tpl_obj, $_scope);
                } else {
                    $output = $tpl_obj->_getCompiledTemplate($this->source, $this->compile_id, $this->caching)->getRenderedTemplate($tpl_obj, $_scope, $scope_type, $data, $no_output_filter);
                }
                // write to cache when necessary
                if (!$this->source->recompiled) {
                    $output = self::$creator[0]->_createCacheFile($this, $tpl_obj, $output, $_scope, $no_output_filter);
                }
                array_shift(self::$creator);
            } else {
                if ($tpl_obj->debugging) {
                    Smarty_Debug::start_cache($this->source);
                }
                $tpl_obj->is_nocache = true;
                try {
                    $level = ob_get_level();
                    array_unshift($tpl_obj->_capture_stack, array());
                    //
                    // render cached template
                    //
                    $output = $this->template_obj->_renderTemplate($tpl_obj, $_scope);
                    // any unclosed {capture} tags ?
                    if (isset($tpl_obj->_capture_stack[0][0])) {
                        $tpl_obj->_capture_error();
                    }
                    array_shift($tpl_obj->_capture_stack);
                } catch (Exception $e) {
                    while (ob_get_level() > $level) {
                        ob_end_clean();
                    }
                    throw $e;
                }
                $tpl_obj->is_nocache = false;
                if ($tpl_obj->debugging) {
                    Smarty_Debug::end_cache($this->source);
                }
            }
            if ($this->template_obj->has_nocache_code && !$no_output_filter && (isset($tpl_obj->autoload_filters['output']) || isset($tpl_obj->registered_filters['output']))) {
                $output = Smarty_Misc_FilterHandler::runFilter('output', $output, $tpl_obj);
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
     *
     * @api
     * @param  Smarty $tpl_obj                  template object
     * @param  integer $_last_modified_timestamp browser cache timestamp
     * @return bool    true if browser cache is valid
     */
    private function checkSubtemplateCache($tpl_obj, $_last_modified_timestamp)
    {
        $subtpl = reset($tpl_obj->cached_subtemplates);
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
            $subtpl = next($tpl_obj->cached_subtemplates);
        }
        // browser cache valid
        return true;
    }

    /**
     * Write this cache object to handler
     *
     * @param  Smarty $tpl_obj template object
     * @param  string $content content to cache
     * @return boolean success
     */
    public function writeCache(Smarty $tpl_obj, $content)
    {
        if (!$this->source->recompiled) {
            if ($this->writeCachedContent($tpl_obj, $content)) {
                $this->timestamp = time();
                $this->exists = true;
                $this->isValid = true;
                if ($tpl_obj->cache_locking) {
                    $this->releaseLock($tpl_obj);
                }

                return true;
            }
        }

        return false;
    }

    /**
     * create Cached Object container
     *
     * @param Smarty $tpl_obj template object
     */
    public static function load(Smarty $tpl_obj, $type = null)
    {

        // todo  check the last cde sequence
        // check runtime cache
        $source_key = $tpl_obj->source->uid;
        $compiled_key = $tpl_obj->compile_id ? $tpl_obj->compile_id : '#null#';
        $cache_key = $tpl_obj->cache_id ? $tpl_obj->cache_id : '#null#';
        if ($tpl_obj->cache_objs && isset(Smarty::$resource_cache[$source_key]['cache'][$compiled_key][$cache_key])) {
            $res_obj = Smarty::$resource_cache[$source_key]['cache'][$compiled_key][$cache_key];
        } else {
            // load Cache resource handler
            $res_obj = Smarty_Resource::loadResource($tpl_obj, $tpl_obj->caching_type, SMARTY::CACHE);
            $res_obj->populate($tpl_obj);
            // save in cache?
            if ($tpl_obj->cache_objs) {
                Smarty::$resource_cache[$source_key]['cache'][$compiled_key][$cache_key] = $res_obj;
            } else {
                // load Cache resource handler
                $res_obj = Smarty_Resource::loadResource($tpl_obj, $tpl_obj->caching_type, SMARTY::CACHE);
                $res_obj->populate($tpl_obj);
                // save in cache?
                if ($tpl_obj->cache_objs) {
                    Smarty::$resource_cache[$source_key]['cache'][$compiled_key][$cache_key] = $res_obj;
                }
            }

            $res_obj->compile_id = $tpl_obj->compile_id;
            $res_obj->cache_id = $tpl_obj->cache_id;

            return $res_obj;

            if (!($tpl_obj->caching == Smarty::CACHING_LIFETIME_CURRENT || $tpl_obj->caching == Smarty::CACHING_LIFETIME_SAVED) || $this->source->recompiled) {
                $res_obj->populate($tpl_obj);

                return;
            }
            while (true) {
                while (true) {
                    $res_obj->populate($tpl_obj);
                    if ($res_obj->timestamp === false || $tpl_obj->force_compile || $tpl_obj->force_cache) {
                        $res_obj->isValid = false;
                    } else {
                        $res_obj->isValid = true;
                    }
                    if ($res_obj->isValid && $tpl_obj->caching == Smarty::CACHING_LIFETIME_CURRENT && $tpl_obj->cache_lifetime >= 0 && time() > ($res_obj->timestamp + $tpl_obj->cache_lifetime)) {
                        // lifetime expired
                        $res_obj->isValid = false;
                    }
                    if ($res_obj->isValid || !$tpl_obj->cache_locking) {
                        break;
                    }
                    if (!$res_obj->locked($tpl_obj)) {
                        $res_obj->acquireLock($tpl_obj);
                        break 2;
                    }
                }
                if ($res_obj->isValid) {
                    if (!$tpl_obj->cache_locking || $res_obj->locked($tpl_obj) === null) {
                        // load cache file for the following checks
                        if ($tpl_obj->debugging) {
                            Smarty_Debug::start_cache($tpl_obj);
                        }
                        if ($res_obj->process($tpl_obj) === false) {
                            $res_obj->isValid = false;
                        }
                        if ($tpl_obj->debugging) {
                            Smarty_Debug::end_cache($tpl_obj);
                        }
                    } else {
                        continue;
                    }
                } else {
                    return;
                }
                if ($res_obj->isValid && $tpl_obj->caching === Smarty::CACHING_LIFETIME_SAVED && $res_obj->template_obj->cache_lifetime >= 0 && (time() > ($res_obj->timestamp + $res_obj->cache_lifetime))) {
                    $res_obj->isValid = false;
                }
                if (!$res_obj->isValid && $tpl_obj->cache_locking) {
                    $res_obj->acquireLock($tpl_obj);

                    return;
                } else {
                    return;
                }
            }
        }
    }
}
