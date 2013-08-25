<?php

// put full path to Smarty.class.php
require '../../distribution/libs/Smarty.class.php';
$smarty = new Smarty();

//$smarty->template_dir = '.';
//$smarty->compile_dir = './cache/smarty';
//$smarty->cache_dir = './cache/imdb';
#$smarty->config_dir = '/web/www.domain.com/smarty/configs';

if (defined('SMARTY_DIR')) echo "SMARTY_DIR is defined: " . SMARTY_DIR . "<br/>";

echo('plugins: ');
var_dump($smarty->plugins_dir);
echo "<br/>";

#$smarty->utilities->test();
//$smarty->test();

readfile($smarty->plugins_dir[0] . 'function.html_image.php');

$smarty->assign('name', 'Ned');
$smarty->display('st.tpl');
