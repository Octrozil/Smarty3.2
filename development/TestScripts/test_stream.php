<?php
/**
 * Test script for stream resources
 *
 * @author Uwe Tews
 * @package SmartyTestScripts
 */

class ResourceStream
{
    private $position;
    private $varname;

    public function stream_open($path, $mode, $options, &$opened_path)
    {
        $url = parse_url($path);
        $this->varname = $url["host"];
        $this->position = 0;

        return true;
    }

    public function stream_read($count)
    {
        $p = & $this->position;
        $ret = substr($GLOBALS[$this->varname]->data, $p, $count);
        $p += strlen($ret);

        return $ret;
    }

    public function stream_write($data)
    {
        $v = & $GLOBALS[$this->varname]->data;
        $l = strlen($data);
        $p = & $this->position;
        $v = substr($v, 0, $p) . $data . substr($v, $p += $l);
        $GLOBALS[$this->varname]->timestamp = time();

        return $l;
    }

    public function stream_tell()
    {
        return $this->position;
    }

    public function stream_eof()
    {
        if (!isset($GLOBALS[$this->varname])) {
            return true;
        }

        return $this->position >= strlen($GLOBALS[$this->varname]->data);
    }

    public function stream_seek($offset, $whence)
    {
        $l = strlen($GLOBALS[$this->varname]);
        $p = & $this->position;
        switch ($whence) {
            case SEEK_SET:
                $newPos = $offset;
                break;
            case SEEK_CUR:
                $newPos = $p + $offset;
                break;
            case SEEK_END:
                $newPos = $l + $offset;
                break;
            default:
                return false;
        }
        $ret = ($newPos >= 0 && $newPos <= $l);
        if ($ret) $p = $newPos;
        return $ret;
    }
}

require '../../distribution/libs/Smarty.class.php';

$smarty = new Smarty;

stream_wrapper_register("global", "ResourceStream")
    or die("Failed to register protocol");
$fp = fopen("global://mytest", "r+");
fwrite($fp, 'hello world {$foo}');
fclose($fp);

//$smarty->debugging = true;
$smarty->assign('foo', 'foo1');

$smarty->display('global:mytest');

$smarty->assign('foo', 'foo2');

$smarty->display('global:mytest');
