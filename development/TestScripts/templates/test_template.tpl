{function name=menu level=0}
    <ul class="level{$level}">
        {foreach $data as $entry}
            {if is_array($entry)}
                <li>{$entry@key}</li>
                {menu data=$entry level=$level+1}
            {else}
                <li>{$entry}</li>
            {/if}
        {/foreach}
    </ul>
{/function}

{$menu = ['item1','item2','item3' => ['item3-1','item3-2','item3-3' => ['item3-3-1','item3-3-2']],'item4']}

{menu data=$menu}
