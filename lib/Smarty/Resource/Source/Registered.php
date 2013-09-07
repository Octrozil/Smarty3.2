<?php

/**
 * Smarty Resource Source Registered Plugin
 *
 * @package Resource\Source
 * @author Uwe Tews
 * @author Rodney Rehm
 */

/**
 * Smarty Resource Source Registered Plugin
 *
 * Implements the registered resource for Smarty template
 *
 *
 * @package Resource\Source
 * @deprecated
 */
class Smarty_Resource_Source_Registered extends Smarty_Resource_Source_File
{
    /**
     *  Smarty object
     *
     * @var Smarty
     */
    public $smarty = null;

    /**
     * populate Source Object with meta data from Resource
     *
     * @param Smarty $smarty Smarty object
     */
    public function populate(Smarty $smarty)
    {
        $this->smarty = $smarty;
        $this->filepath = $this->type . ':' . $this->name;
        $this->uid = sha1($this->filepath);
        if ($smarty->compile_check) {
            $this->timestamp = $this->getTemplateTimestamp();
            $this->exists = !!$this->timestamp;
        }
    }

    /**
     * populate Source Object filepath
     *
     * @param  Smarty $tpl_obj template object
     * @return void
     */
    public function buildFilepath(Smarty $tpl_obj = null)
    {
    }

    /**
     * Get timestamp (epoch) the template source was modified
     *
     * @return integer|boolean timestamp (epoch) the template was modified, false if resources has no timestamp
     */
    public function getTemplateTimestamp()
    {
        // return timestamp
        $time_stamp = false;
        call_user_func_array($this->smarty->registered_resources[Smarty::SOURCE][$this->type][0][1], array($this->name, &$time_stamp, $this->smarty));

        return is_numeric($time_stamp) ? (int)$time_stamp : $time_stamp;
    }

    /**
     * Load template's source by invoking the registered callback into current template object
     *
     * @return string           template source
     * @throws Smarty_Exception if source cannot be loaded
     */
    public function getContent()
    {
        // return template string
        $t = call_user_func_array($this->smarty->registered_resources[Smarty::SOURCE][$this->type][0][0], array($this->name, &$this->content, $this->smarty));
        if (is_bool($t) && !$t) {
            throw new Smarty_Exception("Unable to read template {$this->type} '{$this->name}'");
        }
       return $this->content;
    }

    /**
     * Determine basename for compiled filename
     *
     * @return string resource's basename
     */
    public function getBasename()
    {
        return basename($this->name);
    }
}
