<?php
function smarty_insert_time($params, $smarty)
{
    return 'script ' . $params['var'] . time();
}
