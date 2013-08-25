{if $foo}
    {$foo}
    {block name='title'}Page me {time() nocache} {$smarty.block.parent} {block name='grand'}default grand {/block} Title{/block}
{/if}

{$bar = 'ppppp'}
