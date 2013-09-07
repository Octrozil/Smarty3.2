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
     * populate Source Object with meta data from Resource
     *
     * @param Smarty $smarty Smarty object
     */
    public function populate(Smarty $smarty)
    {
        $this->filepath = $this->buildFilepath($smarty);
        $this->uid = false;
        $this->timestamp = false;
        $this->exists = $this->getContent();
    }

    /**
     * build template filepath by traversing the template_dir array
     *
     * @param  Smarty $smarty template object
     * @return string           fully qualified filepath
     * @throws Smarty_Exception if default template handler is registered but not callable
     */
    public function buildFilepath(Smarty $smarty = null) {
        if (strpos($this->name, '://') !== false) {
            return $this->name;
        } else {
            return str_replace(':', '://', $this->name);
        }
    }

     /**
     * Load template's source from stream into current template object
     *
     * @return string           template source
     * @throws Smarty_Exception if source cannot be loaded
     */
    public function getContent()
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

}
