{include file="findInclude:common/templates/header.tpl"}

{if $terms}
{elseif $termTitle}
<div class="nonfocal"><h3>{$termTitle}</h3></div>
{/if}

{if $courses}
  {include file="findInclude:common/templates/navlist.tpl" navlistItems=$courses subTitleNewline=true}
{elseif $session_userID}
<div class="nonfocal">
{"NO_COURSES"|getLocalizedString}
</div>
{elseif $hasPersonalizedCourses}
<div class="nonfocal">
{"NOT_LOGGED_IN"|getLocalizedString}
</div>
{/if}

{if $catalogItems}
 {include file="findInclude:common/templates/navlist.tpl" navlistItems=$catalogItems}
{/if}
{include file="findInclude:common/templates/footer.tpl"}
