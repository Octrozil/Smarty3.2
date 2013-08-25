<?php
require '../../distribution/libs/Smarty.class.php';
$smarty = new Smarty();
$smarty->addPluginsDir('./myplugins');
if (!$smarty->isCached('report.tpl')) {
    $data = array();
    foreach (array(2003, 2004, 2005) as $year) {
        foreach (array(1, 2, 3, 4) as $quarter) {
            foreach (array('ca', 'us') as $region) {
                foreach (array('foo', 'bar', 'baz') as $item) {
                    $sales = rand(2000, 20000);
                    $data[] = compact('year', 'quarter', 'region', 'item', 'sales');
                }
            }
        }
    }
    $smarty->assign('data', $data);
}

$smarty->display('report.tpl');
