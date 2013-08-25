<?php

require '../../distribution/libs/Smarty.class.php';
$smarty = new Smarty();
$smarty->assign('foo', 'foo');
$smarty->display('string:{$foo}');
