{extends file="gp2.tpl"}
{block content}
    {block header}<h2>{$smarty.block.child}</h2>{/block}
    {block 'ut'}ut{$smarty.block.child}{/block}
    {block 'ut2'}{$smarty.block.child}ut2{/block}
    {block info}
        ---
        {$smarty.block.child}
        ---
    {/block}
    {test header="fake"}
    {block fields hide}
        {$smarty.block.child}
    {/block}
    {/test}
{/block}