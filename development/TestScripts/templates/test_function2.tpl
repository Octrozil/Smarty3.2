{include file="test_function_lib.tpl"}
{function name='test' world='world' loop=3}
    {$loop = $loop + 1}
    {counter}text
    {$world}{$loop}
    uwe
    {nocache}{time()}
    {/nocache}
    <br>
    ja
{/function}

{function name='uwe'}
{/function}

{call name=test world=hallo nocache}

