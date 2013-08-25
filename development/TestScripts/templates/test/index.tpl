<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>Smarty recursion speed test</title>
    <style type="text/css">
        {literal}
        <!--
        body {
            color: #000;
            background: #fff;
            margin: 0;
            padding: 1em;
            font-family: verdana, arial, sans-serif;
        }

        .current {
            font-weight: bold;
            color: red;
        }

        #elapsetime {
            position: fixed;
            bottom: 0px;
            left: 0;
            width: 100%;
            color: red;
            background: yellow;
            font-weight: bold;
            margin: 0;
            padding: 1em;
        }

        -->
        {/literal}
    </style>
</head>

<body>

<h1>Smarty recursion speed test</h1>

<p>Select Smarty version: <span class="current">Smarty 2</span> | <a href="index.php?smarty_version=3">Smarty 3</a></p>

<p>Smarty version used: <strong>{$smarty.version}</strong></p>
{section name=foo loop=100}
    {defun name="tree" data=$my_array level=0}
        <ul class="level{$level}">
            {foreach from=$data key=key item=entry}
                {if is_array($entry)}
                    <li>{$key}</li>
                    {fun name="tree" data=$entry level=$level+1}
                {else}
                    <li>{$entry}</li>
                {/if}
            {/foreach}
        </ul>
    {/defun}
{/section}
{*
<!-- will be closed in index.php after displaying the elapse time -->
</body>
</html>
*}
