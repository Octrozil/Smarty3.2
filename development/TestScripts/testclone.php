<?php
class data {
    function __get($name) {
        return $this->$name = Test::$root->$name;
    }
}
class vari {
    public $value = null;
    function __construct ($value) {
        $this->value = $value;
    }

}


class Test {
    static $root = null;
    public $data = null;
    static $count = 1000;

    function __construct () {
        self::$root = $this->data = new Data();
        for ($i = 0; $i <self::$count; $i++) {
            $n = 'n'.$i;
            $this->data->$n = new vari($i);
        }
    }

    function test1 () {
        echo 'test1<br>';
        $time_start = microtime(true);
        $c = clone $this->data;
        for ($i = 0; $i <self::$count; $i++) {
            $n = 'n'.$i;
            $k = $c->$n->value;
        }
        $time_end = microtime(true);
        $time1 = $time_end - $time_start;
        echo 'zeit '.$time1.'<br><br>';
    }

    function test2 () {
        echo 'test2<br>';
        $time_start = microtime(true);
        $c = new Data();
        for ($i = 0; $i <self::$count; $i++) {
            $n = 'n'.$i;
            $k = $c->$n->value;
        }
        $time_end = microtime(true);
        $time1 = $time_end - $time_start;
        echo 'zeit '.$time1.'<br><br>';
    }
    function test3 () {
        echo 'test3<br>';
        $c = new Data();
        for ($i = 0; $i <self::$count; $i++) {
            $n = 'n'.$i;
            $k = $c->$n;
        }
        $time_start = microtime(true);
        for ($i = 0; $i <self::$count; $i++) {
            $n = 'n'.$i;
            $k = $c->$n->value;
        }
        $time_end = microtime(true);
        $time1 = $time_end - $time_start;
        echo 'zeit '.$time1.'<br><br>';
    }
    function test4 () {
        echo 'test4<br>';
         $time_start = microtime(true);
        for ($i = 0; $i <100; $i++) {
            $c = clone $this->data;
        }
        $time_end = microtime(true);
        $time1 = $time_end - $time_start;
        echo 'zeit '.$time1.'<br><br>';
    }
}

$t = new Test();

$t->test1();
$t->test2();
$t->test3();
$t->test4();