<?php

require_once SMARTY_DIR . '../demo/plugins/cacheresource.mysql.php';

class Smarty_Cache_Resource_Mysqltest extends Smarty_Cache_Resource_Mysql
{
    public function __sleep()
    {
        return array();
    }

    public function __wakeup()
    {
        $this->__construct();
    }
}
