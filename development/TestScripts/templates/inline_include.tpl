{$bar = "hello world"}
<p>I'm inlined {$foo} {$bar}</p>
{include file="inline_time.tpl"}
{if empty($subby)}
    {include file="inline_include.tpl" foo="buh" subby=true inline}
{/if}
<br>end {$foo}