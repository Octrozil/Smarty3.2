<html>
<head>
    <h1>{block name='title'}Default Title {$foo}{/block}</h1>
</head>
{block name='cont'}
    cont parent and {$smarty.block.child}
{/block}
</html>