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
     * @param Smarty            $smarty Smarty object
     * @param Smarty_Source     $source Source object
     * @param Smarty            $parent
     */
    public function populate(Smarty $smarty, Smarty_Source $source, $parent = null)
    {
        $segment = '';

        if ($this->segment) {
            $segment = rtrim($this->segment, "/\\") . '/';
        }

        $source->filepath = $this->directory . $segment . $source->name;

        if ($this->fileExists($source->filepath, $source)) {
            $source->uid = sha1($source->filepath);
        }
    }
}
