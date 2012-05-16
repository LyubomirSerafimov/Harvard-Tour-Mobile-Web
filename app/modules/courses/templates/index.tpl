{include file="findInclude:common/templates/header.tpl"}

{include file="findInclude:modules/courses/templates/termselector.tpl"}

{$tabBodies=array()}
{foreach $tabs as $key}
    {assign var="captureName" value=$key|cat:"Tab"}
    {assign var="templateName" value="index-"|cat:$key|cat:".tpl"}

    {capture name=$captureName assign="tabBody"}
    {include file="findInclude:modules/courses/templates/$templateName"}
    {/capture}

    {$tabBodies[$key] = $tabBody}
{/foreach}
{block name="tabs"}
<div id="tabscontainer">
{include file="findInclude:common/templates/tabs.tpl" tabBodies=$tabBodies smallTabs=true}
</div>
{/block}

{include file="findInclude:common/templates/footer.tpl"}
