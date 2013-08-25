{extends file='test_block_parent.tpl'}

{if $foo}
    {block name='title'}Page me {block name='grand'}default grand {/block}  Title{/block}
{/if}
{$bar = 'ppppp'}
