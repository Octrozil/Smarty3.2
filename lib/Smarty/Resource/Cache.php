<?php

/**
 * Smarty Resource Cache Plugin
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

abstract class Smarty_Resource_Cache  extends Smarty_Exception_Magic
{

    /**
     * resource group
     *
     * @var Smarty_Resource_Loader::Source
     */
    public $resource_group = Smarty_Resource_Loader::Cache;

    /**
     * compiled resource cache
     *
     * @var array
     * @internal
     */
    public static $resource_cache = array();

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
    public $template_obj = uull;

    /**
     * Handler for updating cache files
     * @var array Smarty_Cache_Helper_Create
     */
    public static $creator = array();

    /**
     *
     * @param Smarty $smarty
     * @param Smarty_Source_Resource $source source resource
     * @param Smarty|Smarty_Data|Smarty_Template_Class $parent parent object
     * @param mixed $compile_id  compile id
     * @param mixed $cache_id  compile id
     * @param boolean $caching caching enabled ?
     * @param boolean $isCacheCheck true to just check if cache is valid
     * @return mixed Smarty_Template|false
     */
    static function load(Smarty $smarty, Smarty_Source_Resource $source, $compile_id, $cache_id, $caching)
    {
        // check runtime cache
        $source_key = $source->uid;
        $compiled_key = $compile_id ? $compile_id : '#null#';
        $cache_key = $cache_id ? $cache_id : '#null#';
        if (isset(self::$resource_cache[$source_key][$compiled_key][$cache_key])) {
            return self::$resource_cache[$source_key][$compiled_key][$cache_key];
        }

        self::$resource_cache[$source_key][$compiled_key][$cache_key] = $cache = Smarty_Resource_Loader::load($smarty, Smarty_Resource_Loader::COMPILED);
        $cache->source = $source;
        $cache->compile_id = $compile_id;
        $cache->cache_id = $cache_id;
        $cache->caching = $caching;
        $cache->populate($smarty);

        return $cache;
    }

    /**
     * test if cache is valid
     *
     * @api
     * @param  Smarty $smarty       Smarty object or clone for template
     * @param  string|object $template   the resource handle of the template file or template object
     * @param  mixed $cache_id   cache id to be used with this template
     * @param  mixed $compile_id compile id to be used with this template
     * @param  object $parent     next higher level of Smarty variables
     * @return boolean       cache status
     */
    static function isCached(Smarty $smarty, $template = null, $cache_id = null, $compile_id = null, $parent = null)
    {
        if ($smarty->force_cache || $smarty->force_compile || !($smarty->caching == self::CACHING_LIFETIME_CURRENT || $smarty->caching == self::CACHING_LIFETIME_SAVED)) {
            // caching is disabled
            return false;
        }
        if ($template === null && ($smarty->usage == self::IS_TEMPLATE || $smarty->usage == self::IS_CONFIG)) {
            $template = $smarty;
        }
        if (is_object($template)) {
            // get source from template clone
            $source = $template->source;
            $tpl_obj = $template;
        } else {
            //get source object from cache  or create new one
            $source = Smarty_Resource_Source::load($smarty, $template);
            $tpl_obj = $smarty;
        }
        if ($source->recompiled) {
            // recompiled source can't be cached
            return false;
        }
        $cache = self::load($smarty, $source, isset($compile_id) ? $compile_id : $tpl_obj->compile_id,
            isset($cache_id) ? $cache_id : $tpl_obj->cache_id, $tpl_obj->caching);
        if (!$cache->exists) {
            return false;
        }
        $cache->loadTemplateClass();
        $cache->template_obj = new $cache->class_name($smarty, $parent, $cache->source);
        $class_name = $cache->class_name;
        return $class_name::$isValid;
    }

    /**
     * Read the cached template and process header
     *
     * @param  Smarty $smarty template object
     * @return boolean true or false if the cached content does not exist
     */
    abstract public function process(Smarty $smarty);

    /**
     * Write the rendered template output to cache
     *
     * @param  Smarty $smarty template object
     * @param  string $content content to cache
     * @return boolean success
     */
    abstract public function writeCachedContent(Smarty $smarty, $content);

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

    public function locked(Smarty $smarty)
    {
        // theoretically locking_timeout should be checked against time_limit (max_execution_time)
        $start = microtime(true);
        $hadLock = null;
        while ($this->hasLock($smarty)) {
            $hadLock = true;
            if (microtime(true) - $start > $smarty->locking_timeout) {
                // abort waiting for lock release
                return false;
            }
            sleep(1);
        }

        return $hadLock;
    }

    public function hasLock(Smarty $smarty)
    {
        // check if lock exists
        return false;
    }

    public function acquireLock(Smarty $smarty)
    {
        // create lock
        return true;
    }

    public function releaseLock(Smarty $smarty)
    {
        // release lock
        return true;
    }

    /**
     * Load compiled template
     *
     * @param Smarty                                    $smarty        Smarty object
     * @param Smarty|Smarty_Data|Smarty_Template_Class  $parent         parent object
     * @params boolean                                  $isCacheCheck   true to just check if cache is valid
     * @throws Smarty_Exception_Runtime
     * @return mixed Smarty_Template|false
     */
    public function loadTemplate($smarty, $parent, $isCacheCheck)
    {
        if ($isCacheCheck && (!$this->exists || !$this->caching || $smarty->force_compile || $smarty->force_cache || $this->source->recompiled)) {
            return false;
        }
        try {
            $level = ob_get_level();
            $isValid = false;
            if ($this->exists && !$smarty->force_compile && !$smarty->force_cache) {
                $this->process($smarty);
                $template_obj = new $this->class_name($smarty, $parent, $this->source);
                $class_name = $this->class_name;
                $isValid = $class_name::$isValid;
            }
            if ($isCacheCheck) {
                return $isValid ? $template_obj : false;
            }
            if (!$isValid) {
                $smarty->_loadCompiledTemplate($source, $parent, $compile_id)->getRenderedTemplate($scope_type, $data, $no_output_filter);
                if ($smarty->debugging) {
                    Smarty_Debug::start_compile($this->source);
                }
                $compiler = Smarty_Compiler::load($smarty, $this->source, $this->caching);
                $compiler->compileTemplateSource($smarty->_loadResource(Smarty::COMPILED)->populateResource($smarty, $this->source, $this->compile_id, $this->caching));
                unset($compiler);
                if ($smarty->debugging) {
                    Smarty_Debug::end_compile($this->source);
                }
                $this->process($smarty);
                $template_obj = new $this->class_name($smarty, $parent, $this->source);
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
     * @param  Smarty $smarty          template object
     * @param  Smarty_Variable_Scope $_scope
     * @param  int $scope_type
     * @param  array $data             array with variable names and values which must be assigned
     * @param  bool $no_output_filter flag that output filter shall be ignored
     * @param  bool $display
     * @throws Exception
     * @return bool|string
     */
    public function getRenderedTemplate($smarty, $_scope, $scope_type, $data, $no_output_filter, $display)
    {
        $_scope = $smarty->_buildScope($_scope, $scope_type, $data);
        $browser_cache_valid = false;
        if ($display && $smarty->cache_modified_check && $this->isValid && !$this->template_obj->has_nocache_code) {
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
            if (!$this->isValid) {
                // unshift new handler for cache creation in first position
                // cache could be nested as subtemplates can have individual cache
                array_unshift(self::$creator, new Smarty_Cache_Helper_Create());
                if ($this->source->uncompiled) {
                    $output = $this->source->getRenderedTemplate($smarty, $_scope);
                } else {
                    $output = $smarty->_getCompiledTemplate($this->source, $this->compile_id, $this->caching)->getRenderedTemplate($smarty, $_scope, $scope_type, $data, $no_output_filter);
                }
                // write to cache when necessary
                if (!$this->source->recompiled) {
                    $output = self::$creator[0]->_createCacheFile($this, $smarty, $output, $_scope, $no_output_filter);
                }
                array_shift(self::$creator);
            } else {
                if ($smarty->debugging) {
                    Smarty_Debug::start_cache($this->source);
                }
                $smarty->is_nocache = true;
                try {
                    $level = ob_get_level();
                    array_unshift($smarty->_capture_stack, array());
                    //
                    // render cached template
                    //
                    $output = $this->template_obj->_renderTemplate($smarty, $_scope);
                    // any unclosed {capture} tags ?
                    if (isset($smarty->_capture_stack[0][0])) {
                        $smarty->_capture_error();
                    }
                    array_shift($smarty->_capture_stack);
                } catch (Exception $e) {
                    while (ob_get_level() > $level) {
                        ob_end_clean();
                    }
                    throw $e;
                }
                $smarty->is_nocache = false;
                if ($smarty->debugging) {
                    Smarty_Debug::end_cache($this->source);
                }
            }
            if ($this->template_obj->has_nocache_code && !$no_output_filter && (isset($smarty->autoload_filters['output']) || isset( $smarty->smarty_extensions['Smarty_Extension_Filter']->registered_filters['output']))) {
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
     *
     * @api
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
     * Write this cache object to handler
     *
     * @param  Smarty $smarty template object
     * @param  string $content content to cache
     * @return boolean success
     */
    public function writeCache(Smarty $smarty, $content)
    {
        if (!$this->source->recompiled) {
            if ($this->writeCachedContent($smarty, $content)) {
                $this->timestamp = time();
                $this->exists = true;
                $this->isValid = true;
                if ($smarty->cache_locking) {
                    $this->releaseLock($smarty);
                }

                return true;
            }
        }

        return false;
    }

    /**
     * create Cached Object container
     *
     * @param Smarty $smarty template object
     */
    public static function loado(Smarty $smarty, $type = null)
    {

        // todo  check the last cde sequence
        // check runtime cache
        $source_key = $smarty->source->uid;
        $compiled_key = $smarty->compile_id ? $smarty->compile_id : '#null#';
        $cache_key = $smarty->cache_id ? $smarty->cache_id : '#null#';
        if ($smarty->cache_objs && isset(Smarty_Resource_Loader::$resource_cache[$source_key]['cache'][$compiled_key][$cache_key])) {
            $res_obj = Smarty_Resource_Loader::$resource_cache[$source_key]['cache'][$compiled_key][$cache_key];
        } else {
            // load Cache resource handler
            $res_obj = Smarty_Source_Resource::loadResource($smarty, $smarty->caching_type, SMARTY::CACHE);
            $res_obj->populate($smarty);
            // save in cache?
            if ($smarty->cache_objs) {
                Smarty_Resource_Loader::$resource_cache[$source_key]['cache'][$compiled_key][$cache_key] = $res_obj;
            } else {
                // load Cache resource handler
                $res_obj = Smarty_Source_Resource::loadResource($smarty, $smarty->caching_type, SMARTY::CACHE);
                $res_obj->populate($smarty);
                // save in cache?
                if ($smarty->cache_objs) {
                    Smarty_Resource_Loader::$resource_cache[$source_key]['cache'][$compiled_key][$cache_key] = $res_obj;
                }
            }

            $res_obj->compile_id = $smarty->compile_id;
            $res_obj->cache_id = $smarty->cache_id;

            return $res_obj;

            if (!($smarty->caching == Smarty::CACHING_LIFETIME_CURRENT || $smarty->caching == Smarty::CACHING_LIFETIME_SAVED) || $this->source->recompiled) {
                $res_obj->populate($smarty);

                return;
            }
            while (true) {
                while (true) {
                    $res_obj->populate($smarty);
                    if ($res_obj->timestamp === false || $smarty->force_compile || $smarty->force_cache) {
                        $res_obj->isValid = false;
                    } else {
                        $res_obj->isValid = true;
                    }
                    if ($res_obj->isValid && $smarty->caching == Smarty::CACHING_LIFETIME_CURRENT && $smarty->cache_lifetime >= 0 && time() > ($res_obj->timestamp + $smarty->cache_lifetime)) {
                        // lifetime expired
                        $res_obj->isValid = false;
                    }
                    if ($res_obj->isValid || !$smarty->cache_locking) {
                        break;
                    }
                    if (!$res_obj->locked($smarty)) {
                        $res_obj->acquireLock($smarty);
                        break 2;
                    }
                }
                if ($res_obj->isValid) {
                    if (!$smarty->cache_locking || $res_obj->locked($smarty) === null) {
                        // load cache file for the following checks
                        if ($smarty->debugging) {
                            Smarty_Debug::start_cache($smarty);
                        }
                        if ($res_obj->process($smarty) === false) {
                            $res_obj->isValid = false;
                        }
                        if ($smarty->debugging) {
                            Smarty_Debug::end_cache($smarty);
                        }
                    } else {
                        continue;
                    }
                } else {
                    return;
                }
                if ($res_obj->isValid && $smarty->caching === Smarty::CACHING_LIFETIME_SAVED && $res_obj->template_obj->cache_lifetime >= 0 && (time() > ($res_obj->timestamp + $res_obj->cache_lifetime))) {
                    $res_obj->isValid = false;
                }
                if (!$res_obj->isValid && $smarty->cache_locking) {
                    $res_obj->acquireLock($smarty);

                    return;
                } else {
                    return;
                }
            }
        }
    }
}
