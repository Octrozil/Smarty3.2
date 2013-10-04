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
     * @param Smarty_Context $context
     */
    public function populate(Smarty_Context $context)
    {
        $segment = '';

        if ($this->segment) {
            $segment = rtrim($this->segment, "/\\") . '/';
        }

        $context->filepath = $this->directory . $segment . $context->name;

        if ($this->fileExists($context->filepath, $context)) {
            $context->uid = sha1($context->filepath);
        }
    }
}
