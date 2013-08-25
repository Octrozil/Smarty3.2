{extends file="gp.tpl"}
{block name=content}
    some content here

    {block name=section1}
        blabla
    {/block}

    {block name=section2}
        Sect2 -- {$smarty.block.child} ---
    {/block}

{/block}