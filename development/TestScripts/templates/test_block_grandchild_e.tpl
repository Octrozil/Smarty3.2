{extends file='test_block_child_e.tpl'}
{block name='grand'}Grandchild Page Title{$smarty.block.parent}{/block}
{block name='cont'}
    cont from grand
{/block}
