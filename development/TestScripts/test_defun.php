<?php
$start = microtime(true);

require '../../distribution/libs/Smarty.class.php';

$smarty = new Smarty;
$smarty->plugins_dir[] = './plugins';
$smarty->force_compile = true;

$my_array = array('item 1',
    'item 2' => array('item 2.1' => array('item 2.1.1'
    )
    ),
    'item 3' => array('item 3.1',
        'item 3.2',
        'item 3.3' => array('item 3.3.1',
            'item 3.3.2' => array('item 3.3.2.1' => array('item 3.3.2.1.1'
            )
            ),
            'item 3.3.3'
        )
    ),
    'item 4'
);
// echo '<pre>';
// print_r($my_array);
// exit;
$smarty->assign('my_array', $my_array);

$smarty->display('test/index.tpl');

$elapsetime = microtime(true) - $start;
echo '<div id="elapsetime">Elapse time: ' . $elapsetime . '</p>';

?></body>
</html>
