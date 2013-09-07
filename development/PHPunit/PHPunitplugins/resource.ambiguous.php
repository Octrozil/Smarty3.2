<?php

/**
 * Ambiguous Filename Custom Resource Example
 *
 * @package Resource-examples
 * @author Rodney Rehm
 */
class Smarty_Resource_Source_Ambiguous extends Smarty_Resource_Source_File
{

    protected $directory;
    protected $segment;

    public function __construct($directory)
    {
        $this->directory = rtrim($directory, "/\\") . '/';
    }

    public function setSegment($segment)
    {
        $this->segment = $segment;
    }

    /**
     * populate Source Object with meta data from Resource
     *
     * @param Smarty $_template template object
     */
    public function populate(Smarty $tpl_obj = null)
    {
        $segment = '';
        if ($this->segment) {
            $segment = rtrim($this->segment, "/\\") . '/';
        }

        $this->filepath = $this->directory . $segment . $this->name;
        $this->uid = sha1($this->filepath);
        if ($tpl_obj->compile_check && !isset($this->timestamp)) {
            $this->timestamp = @filemtime($this->filepath);
            $this->exists = !!$this->timestamp;
        }
    }
}
