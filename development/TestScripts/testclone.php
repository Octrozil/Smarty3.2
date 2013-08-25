<?php

class Test1{
    static $j = 0;
    public static function test(){
        for($i=0; $i<=1000; $i++)
            self::$j += $i;
    }
}

class Test2{
    public $j = 0;
    public function test() {
        for ($i=0; $i<=1000; $i++){
            $this->j += $i;
        }
    }

}

$time_start = microtime();
$test1 = new Test2();
for($i=0; $i<=100;$i++)
    $test1->test();
$time_end = microtime();

$time1 = $time_end - $time_start;

$time_start = microtime();
for($i=0; $i<=100;$i++)
    Test1::test();
$time_end = microtime();

$time2 = $time_end - $time_start;
$time = $time1 - $time2;
$t = $time/$time1;
echo "$time1  $time2 Difference: $time   {$t}";