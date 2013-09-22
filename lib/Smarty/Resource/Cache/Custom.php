<?php

/**
 * Smarty Internal Plugin
 *
 * @package Resource\Cache
 * @author Rodney Rehm
 * @author Uwe Tews
 * */

/**
 * Cache Handler API
 *
 * @package Resource\Cache
 */
abstract class Smarty_Resource_Cache_Custom extends Smarty_Resource_Cache_File
{
    /**
     * Cache Filepath
     * @var string
     */
    public $filepath = false;

    /**
     * Cache  Content
     * @var string
     */
    public $content = null;

    /**
     * Cache Id
     * @var mixed
     */
    public $cache_id = null;

    /**
     * Compile Id
     * @var mixed
     */
    public $compile_id = null;

    /**
     * Cache  Timestamp
     * @var integer
     */
    public $timestamp = false;

    /**
     * Cache  Existence
     * @var boolean
     */
    public $exists = false;

    /**
     * Source object
     * @var boolean
     */
    public $source = null;

    /**
     * fetch cached content and its modification time from data source
     *
     * @param  string $id         unique cache content identifier
     * @param  string $name       template name
     * @param  string $cache_id   cache id
     * @param  string $compile_id compile id
     * @param  string $content    cached content
     * @param  integer $mtime      cache modification timestamp (epoch)
     * @return void
     */
    abstract protected function fetch($id, $name, $cache_id, $compile_id, &$content, &$mtime);

    /**
     * Fetch cached content's modification timestamp from data source
     *
     * {@internal implementing this method is optional.
     *  Only implement it if modification times can be accessed faster than loading the complete cached content.}}
     *
     * @param  string $id         unique cache content identifier
     * @param  string $name       template name
     * @param  string $cache_id   cache id
     * @param  string $compile_id compile id
     * @return integer|boolean timestamp (epoch) the template was modified, or false if not found
     */
    protected function fetchTimestamp($id, $name, $cache_id, $compile_id)
    {
        return null;
    }

    /**
     * Save content to cache
     *
     * @param  string $id         unique cache content identifier
     * @param  string $name       template name
     * @param  string $cache_id   cache id
     * @param  string $compile_id compile id
     * @param  integer|null $exp_time   seconds till expiration or null
     * @param  string $content    content to cache
     * @return boolean      success
     */
    abstract protected function save($id, $name, $cache_id, $compile_id, $exp_time, $content);

    /**
     * Delete content from cache
     *
     * @param  string $name       template name
     * @param  string $cache_id   cache id
     * @param  string $compile_id compile id
     * @param  integer|null $exp_time   seconds till expiration time in seconds or null
     * @return integer      number of deleted caches
     */
    abstract protected function delete($name, $cache_id, $compile_id, $exp_time);

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
        $this->source = $source;
        $this->compile_id = isset($compile_id) ? preg_replace('![^\w\|]+!', '_', $compile_id) : null;
        $this->cache_id = isset($cache_id) ? preg_replace('![^\w\|]+!', '_', $cache_id) : null;
        return $this->filepath = sha1($this->source->filepath . $this->cache_id . $this->compile_id);
    }

    /**
     * populate Cached Object with meta data from Resource
     *
     * @param  Smarty $tpl_obj
     * @return void
     */
    public function populate(Smarty $tpl_obj)
    {
        $_cache_id = isset($this->cache_id) ? preg_replace('![^\w\|]+!', '_', $this->cache_id) : null;
        $_compile_id = isset($this->compile_id) ? preg_replace('![^\w\|]+!', '_', $this->compile_id) : null;

        $this->filepath = sha1($this->source->filepath . $_cache_id . $_compile_id);
        $this->populateTimestamp($tpl_obj);
    }

    /**
     * populate Cached Object with timestamp and exists from Resource
     *
     * @param  Smarty $tpl_obj template object
     * @return void
     */
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
        $mtime = $this->fetchTimestamp($this->filepath, $this->source->name, $this->cache_id, $this->compile_id);
        if ($mtime !== null) {
            $timestamp = $mtime;
            $exists = !!$timestamp;

            return;
        }
        $timestamp = null;
        $this->fetch($this->filepath, $this->source->name, $this->cache_id, $this->compile_id, $this->content, $timestamp);
        $timestamp = isset($timestamp) ? $timestamp : false;
        $exists = !!$timestamp;
    }

    /**
     * Read the cached template and process the header
     *
     * @param  Smarty $tpl_obj template object
     * @return bool   true or false if the cached content does not exist
     */
    public function process(Smarty $tpl_obj)
    {
        if (isset($this->content)) {
            $content = $this->content;
            $this->content = null;
        } else {
            $content = null;
        }
        $timestamp = $this->timestamp ? $this->timestamp : null;
        if ($content === null || !$timestamp) {
            $this->fetch(
                $this->filepath, $this->source->name, $this->cache_id, $this->compile_id, $content, $timestamp
            );
        }
        if (isset($content)) {
            eval("?>" . $content);

            return true;
        }

        return false;
    }

    /**
     * Write the rendered template output to cache
     *
     * @param  Smarty $tpl_obj template object
     * @param  string $content content to cache
     * @return boolean success
     */
    public function writeCachedContent(Smarty $tpl_obj, $content)
    {
        return $this->save(
            $this->filepath, $this->source->name, $this->cache_id, $this->compile_id, $this->template_obj->cache_lifetime, $content
        );
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
        return $this->delete(null, null, null, $exp_time);
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
        return $this->delete($resource_name, $cache_id, $compile_id, $exp_time);
    }

    /**
     * Check is cache is locked for this template
     *
     * @param  Smarty $tpl_obj template object
     * @return bool   true or false if cache is locked
     */
    public function hasLock(Smarty $tpl_obj)
    {
        $id = $this->filepath;
        $name = $this->source->name . '.lock';

        $mtime = $this->fetchTimestamp($id, $name, null, null);
        if ($mtime === null) {
            $this->fetch($id, $name, null, null, $content, $mtime);
        }

        return $mtime && time() - $mtime < $tpl_obj->locking_timeout;
    }

    /**
     * Lock cache for this template
     *
     * @param  Smarty $tpl_obj Smarty object
     * @return void
     */
    public function acquireLock(Smarty $tpl_obj)
    {
        $tpl_obj->is_locked = true;

        $id = $this->filepath;
        $name = $this->source->name . '.lock';
        $this->save($id, $name, null, null, $tpl_obj->locking_timeout, '');
    }

    /**
     * Unlock cache for this template
     *
     * @param  Smarty $tpl_obj template object
     * @return void
     */
    public function releaseLock(Smarty $tpl_obj)
    {
        $tpl_obj->is_locked = false;

        $name = $this->source->name . '.lock';
        $this->delete($name, null, null, null);
    }

}
