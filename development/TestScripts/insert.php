<?php
function insert_mytime($params, $smarty, $template)
{
    return $params['var'] . time();
}
