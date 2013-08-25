{extends file='parent.tpl'}
{block name='content' append}

    {function name='just_to_test'}
        Title we passed to the function: {$title}
    {/function}

    {just_to_test title='test'}
{/block}
{block name='content2'}

    {function name='test2'}
        Title we passed to test2: {$title}
    {/function}

    {test2 title='test2'}
{/block}
{block name='content3'}
    {include file='inc.tpl'}
    {nocache}{test3 title='test3'}{/nocache}
{/block} 