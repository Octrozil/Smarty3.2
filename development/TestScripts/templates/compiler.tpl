Compiler performance test<br>
{assign var=foo value=1}Eins {$foo}<br>
{$x=2}Zwei {$x}<br>
Loop foreach {foreach item=y from=[1,2,3,4,5,6,7]}{$y}{$y@index}{/foreach}<br>
{include file='include2.tpl'}
