{include file="findInclude:common/templates/header.tpl"}

{include file="findInclude:modules/courses/templates/termselector.tpl"}

{capture assign=tabBody}
{if $tasks}
{block name="groupSelector"}
<ul class="tabstrip threetabs">
{foreach $groupLinks as $index => $groupLink}
<li{if $group == $index} class="active"{/if}><a href="{$groupLink.url}">By {$groupLink.title}</a>
{/foreach}
</ul>
{/block}
{foreach $tasks as $group}
    {$navListHeading=$group.title|default:''}
    {include file="findInclude:common/templates/navlist.tpl" navListHeading=$navListHeading navlistItems=$group.items subTitleNewline=true}
{/foreach}
{else}
{"NO_TASKS"|getLocalizedString}
{/if}
{/capture}
{include file="findInclude:modules/courses/templates/courseTabs.tpl" tabBody=$tabBody}

{include file="findInclude:common/templates/footer.tpl"}
