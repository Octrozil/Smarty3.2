{extends file='grand_parent_bug.tpl'}
{block name='top'}
    This is not shown too: {$test|default:'testing'}
{/block}
{block name='content'}
{/block} 