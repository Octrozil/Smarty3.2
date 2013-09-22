<?php

/**
 * Smarty Resource Source Stream Plugin
 *
 * Implements the streams as resource for Smarty template
 *
 * @package Resource\Source
 * @author Uwe Tews
 * @author Rodney Rehm
 */

/**
 * Smarty Resource Source Stream Plugin
 *
 * Implements the streams as resource for Smarty template
 *
 * @link http://php.net/streams
 *
 * @package Resource\Source
 */
class Smarty_Resource_Source_Stream extends Smarty_Resource_Source_File
{
    /**
     * Flag that source must always be recompiled
     *
     * @var bool
     */
    public $recompiled = true;

    /**
     * Content from stream resource
     *
     * @var string
     */
    public $content  = null;

    /**
     * This resource allows relative path
     *
     * @var false
     */
    public $_allow_relative_path = false;

    /**
     * populate Source Object with meta data from Resource
     *
     * @param Smarty            $smarty Smarty object
     * @param Smarty_Source     $source Source object
     * @param Smarty            $parent
     */
    public function populate(Smarty $smarty, Smarty_Source $source, $parent = null)
    {
        $source->filepath = $this->buildFilepath($smarty, $source, $parent);
        $source->uid = false;
        $source->timestamp = false;
        $source->exists = $this->getContent($source);
    }

    /**
     * build template filepath by traversing the template_dir array
     *
     * @param  Smarty           $smarty template object
     * @param  Smarty_Source    $source Source object
     * @return string           fully qualified filepath
     */
    public function buildFilepath(Smarty $smarty, $source, $parent = null) {
        if (strpos($source->name, '://') !== false) {
            return $source->name;
        } else {
            return str_replace(':', '://', $source->name);
        }
    }

     /**
     * Load template's source from stream into current template object
     *
     * @return string           template source
     * @throws Smarty_Exception if source cannot be loaded
     */
    /**
     * Load template's source from stream into current template object
     *
     * @param Smarty_Source $source
     * @return boolean false|string
     */
    public function getContent($source)
    {
        if ($this->content !== null) {
            return $this->content;
        }
        // the availability of the stream has already been checked in Smarty_Resource_Source::fetch()
        $fp = fopen($this->filepath, 'r+');
        if ($fp) {
            $this->content = '';
            while (!feof($fp) && ($current_line = fgets($fp)) !== false) {
                $this->content .= $current_line;
            }
            fclose($fp);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine basename for compiled filename
     *
     * Always returns an empty string.
     *
     * @param Smarty_Source $source
     * @return string resource's basename
     */
    public function getBasename($source)
    {
        return '';
    }
}
