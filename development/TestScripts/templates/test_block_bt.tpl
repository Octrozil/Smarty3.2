{extends file='test_block_pa.tpl'}
{block name=test}
    %%% {$foo} {$smarty.block.child} {time() nocache}  test block bt %%%%
    <br>
{/block}
