<?php

require_once '../../demo/plugins/cacheresource.mysql.php';

class Smarty_Resource_Cache_Mysqltest extends Smarty_Resource_Cache_Mysql
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
