{extends file="outer.tpl"}
{block name=dependency}
    {if $PAGE.NAME != 'espiams'}
        xxxxx
    {/if}

    {if true}
        {$smarty.block.parent}
    {/if}


{/block} 