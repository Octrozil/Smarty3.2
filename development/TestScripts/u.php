<?php
function u($b)
{
    echo $b;
}

$reflection = new ReflectionFunction('u');
echo $reflection->getStartLine();
$i = 1;
