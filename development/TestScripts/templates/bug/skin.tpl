{block name=body}
    directory {$smarty.current_dir}
    <br>
    template {$smarty.template}
    <br>
    <body class="skin1">
    <div class="skin2">
        {block name=content}{/block}
    </div>
    </body>
{/block}