{extends file='parent_nested.tpl'}

{block name='content'}*** begin pretty border ***{$smarty.block.parent}*** end pretty border ***{block name='uwe'}nogood{/block} {/block}
