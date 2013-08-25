<?php
$files = glob('../../../distribution/libs/Smarty.class.php');
foreach ($files as $file) {
    shell_exec('C:\wamp\bin\php\php6.0dev\php.exe>doclog.txt src/phpcompactor.php SmartyCompressed.php "' . escapeshellcmd($file) . '"');
}
