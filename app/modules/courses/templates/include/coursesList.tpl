{if $courses}
{if $courseListHeading}
  <div class="nonfocal">
    <h3>{$courseListHeading}</h3>
  </div>
{/if}
<ul class="nav">
{foreach $courses as $course}
<li class="statusitem update update_{$course.type}">
  <a {block name="courselinkAttrs"}href="{$course.url}"{/block}>
    {$course.title}
    <div class="smallprint courseListUpdates {if $course.img}icon{/if}">
    {if $course.img}<img src="{$course.img}" width="16" height="16" alt="" class="listtype">{/if}
    {include file="findInclude:modules/courses/templates/include/coursesListItemSubtitle.tpl" subtitle=$course.subtitle}
    </div>
  </a>
</li>
{/foreach}
</ul>
{/if}
