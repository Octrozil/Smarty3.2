{extends file='test_block_b.tpl'}
<title>{block name='title'}{/block}</title>
<h1>{block name='headline'}{$smarty.block.child}{/block}</h1>
{block name="description"}{/block}<br>
{block name="hide" append hide}hide s{/block}<br>
