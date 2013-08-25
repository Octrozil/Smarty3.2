{extends 'parent_exception.tpl'}

{block name='uwe'}





{/block}

{block name='test'}
    ja
    {nocache}
        {if $foo}{$b}{/if}
    {/nocache}
    {counter}
    nein
{/block}