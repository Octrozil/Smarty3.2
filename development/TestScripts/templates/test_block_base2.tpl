<title>{block name='title'}-- default title base2 --{/block}</title>
<h1>{block name='headline'}-- default headline --{/block}</h1>
{block name="description"}-- default description --{/block}<br>
{block name='dummy'}{counter}-- local block --{/block}<br>
<br><br>Main<br>
{block name='main'}
    {function name='func1'}
        PLC
    {/function}
    hallo
{/block}

{block name='content'}
{/block} 