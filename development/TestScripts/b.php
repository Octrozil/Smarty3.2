<?php
for ($i = 0; $i < 5000; $i++) {
    $_tmp_file = tempnam('./', 'wrt');

    if (!($fd = @fopen($_tmp_file, 'wb'))) {
        throw new Smarty_Exception("unable to write file {$_tmp_file}");

        return false;
    }

    fwrite($fd, 'hallo world');
    fclose($fd);

    // remove original file
    if (file_exists('uwe.txt'))
        @unlink('uwe.txt');
    // rename tmp file
    rename($_tmp_file, 'uwe.txt');

}
