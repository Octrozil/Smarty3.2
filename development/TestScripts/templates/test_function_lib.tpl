{function name='test' world='world' loop=0}
    {$loop = $loop + 1}
    {counter}text
    {$world}{$loop}
    <br>
    {foreach $bar as $i nocache}
        {$i}
    {/foreach}
    {call 'uwe' nocache}
{/function}

{function name='uwe'}
    <br>
    {nocache}{time()}{/nocache}
    <br>
{/function}