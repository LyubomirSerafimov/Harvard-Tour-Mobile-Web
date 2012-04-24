{block name="courseList"}
{if $coursesLinks}
    {include file="findInclude:modules/courses/templates/coursesList.tpl"  courses=$coursesLinks}
{elseif $session_userID}
    <div>
    {"NO_COURSES"|getLocalizedString}
    </div>
{elseif $hasPersonalizedCourses}
    {block name="loginText"}
        {block name="welcomeInfo"}
        <div class="nonfocal">
            <h2>{$welcomeTitle}</h2>
            <p>{$welcomDesription}</p>
        </div>
        {/block}
        <div>
        {include file="findInclude:common/templates/navlist.tpl" navlistItems=$loginLink navListHeading=$loginText subTitleNewline=true}
        </div>
    {/block}
{/if}
{/block}
{block name="courseCatalog"}
{if $catalogItems}
    {include file="findInclude:common/templates/navlist.tpl" navListHeading=$courseCatalogText navlistItems=$catalogItems}
{/if}
{/block}
