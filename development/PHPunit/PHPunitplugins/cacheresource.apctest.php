<?php

require_once Smarty_Autoloader::$smarty_path . '../demo/plugins/cacheresource.apc.php';

class Smarty_Resource_Cache_Apctest extends Smarty_Resource_Cache_Apc
{
    public function get(Smarty $_template)
    {
        $this->contents = array();
        $this->timestamps = array();
        $t = $this->getContent($_template);

        return $t ? $t : null;
    }

    public function __sleep()
    {
        return array();
    }

    public function __wakeup()
    {
        $this->__construct();
    }
}
