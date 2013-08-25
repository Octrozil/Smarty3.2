{function name='create_menu' level=0}

    {foreach $data as $entry}
        {if $entry.type == 'dir'}
            {if count($entry.content) > 0}
                <div class="menu_entry menu_entry_level_{$level} menu_entry_dir">{$entry.name}</div>
                {call name='create_menu' data=$entry.content level=$level+1}
            {/if}
        {else}
            <div class="menu_entry menu_entry_level_{$level} menu_entry_page">
                <a href="{$entry.link}">{$entry.name|escape:'html'}</a>
            </div>
        {/if}
    {/foreach}
{/function}

<html>
<head>

    <title>{block name='title'}WebComicsStore.com{/block}</title>

    <meta name="title" content="{block name='meta_title'}WebComicsStore.com{/block}"></meta>
    <meta name="description" content="{block name='meta_description'}WebComicsStore.com{/block}"></meta>
    <meta name="keywords" content="{block name='meta_keywords'}WebComicsStore.com{/block}"></meta>

</head>
<body>
<table id="framework">
    <tr>
        <td id="header" colspan="2">
            WebComicsStore.com
        </td>
    </tr>
    <tr>
        <td id="menu">
            {call name='create_menu' data=$pages_structure}
        </td>
        <td id="content">
            {block name='content'}
                <p>Welcome on WebComicsStore.com</p>
            {/block}
        </td>
    </tr>
</table>
</body>
</html>
