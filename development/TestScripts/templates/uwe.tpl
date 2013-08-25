{$foo=[1,2,3,4]}
{foreach $foo as $k}
    {$x = $k cachevalue}
    {nocache}
        {$x}
    {/nocache}
{/foreach}