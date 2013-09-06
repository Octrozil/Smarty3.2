<?php

require_once Smarty_Autoloader::$smarty_path . '../demo/plugins/resource.mysqls.php';

class Smarty_Resource_Mysqlstest extends Smarty_Resource_Mysqls
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
