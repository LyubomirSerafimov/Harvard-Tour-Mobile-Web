{include file="findInclude:common/templates/header.tpl"}

{include file="findInclude:modules/courses/templates/coursedetailhead.tpl"}

{if $contentTypes}
    <h2 class="nonfocal">{"CONTENT_TYPE_TITLE"|getLocalizedString}</h2>
    {include file="findInclude:common/templates/navlist.tpl" navlistItems=$contentTypes} 
{/if}

{capture assign=tabBody}
{if $contents}
{include file="findInclude:common/templates/navlist.tpl" navlistItems=$contents subTitleNewline=true}
{else}
{"NO_UPDATES"|getLocalizedString}
{/if}
{/capture}
{include file="findInclude:modules/courses/templates/courseTabs.tpl" tabBody=$tabBody}

{include file="findInclude:common/templates/footer.tpl"}
