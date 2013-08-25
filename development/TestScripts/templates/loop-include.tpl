<ul>
  {foreach $values as $key => $value}
    {include file="helloworld.tpl" key=$key value=$value}
  {/foreach}
</ul>