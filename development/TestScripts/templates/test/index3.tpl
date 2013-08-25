<h1>Smarty recursion speed test</h1>


<p>Smarty version used: <strong>{$smarty.version}</strong></p>
{function name="tree" data=$my_array level=0}
    <ul class="level{$level}">
        {foreach from=$data key=key item=entry}
            {if is_array($entry)}
                <li>{$key}</li>
                {call name="tree" data=$entry level=$level+1}
            {else}
                <li>{$entry}</li>
            {/if}
        {/foreach}
    </ul>
{/function}

{section name=foo loop=100}
    {call name="tree"}
{/section}

{*
<!-- will be closed in index.php after displaying the elapse time -->
</body>
</html>
*}
