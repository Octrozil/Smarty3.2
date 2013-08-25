{if $topicSources|count > 0}
    {$firstTopicUrl = "articles?topic_id=`$topic->topic_id`"}
{else}
    {$firstTopicUrl = "about:blank"}
{/if}
<iframe name="articles" src="{$firstTopicUrl}" width="100%" height="250" class="articles"></iframe> 