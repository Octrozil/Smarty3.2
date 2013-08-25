Test of function plugin<br>
{nocache}
    {counter assign=foo start=10 skip=5}
    <br>
    {$foo}
    {counter}
    <br>
    {$foo}
{/nocache}
