<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>inline</title>
    <meta name="title" content=""/>
    <meta name="description" content=""/>

    <link rel="stylesheet" type="text/css" href="screen.css"/>
</head>
<body>
{$bar = "red green blue"}
{include file="inline_include.tpl" foo="bar" inline}
<p>{$bar}</p>
</body>
</html>