{include file="findInclude:common/templates/header.tpl"}

{include file="findInclude:modules/courses/templates/termselector.tpl"}

{capture assign=tabBody}
    {if $courses}
        {include file="findInclude:common/templates/navlist.tpl" navListHeading=$termTitle navlistItems=$courses subTitleNewline=true}
    {elseif $session_userID}
        <div>
        {"NO_COURSES"|getLocalizedString}
        </div>
    {elseif $hasPersonalizedCourses}
        {block name="loginText"}
            <div>
            {include file="findInclude:common/templates/navlist.tpl" navlistItems=$loginLink navListHeading=$loginText subTitleNewline=true}
            </div>
        {/block}
    {/if}

    {if $catalogItems}
        {include file="findInclude:common/templates/navlist.tpl" navListHeading=$courseCatalogText navlistItems=$catalogItems}
    {/if}
{/capture}
{include file="findInclude:modules/courses/templates/courseTabs.tpl" tabBody=$tabBody}

{include file="findInclude:common/templates/footer.tpl"}
