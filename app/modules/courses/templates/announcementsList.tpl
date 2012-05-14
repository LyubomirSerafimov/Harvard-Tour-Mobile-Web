{if $announcements}
<ul class="nav">
{foreach $announcements as $announcement}
<li class="announcement announcement_{$announcement.type}">
  <a href="{$announcements.url}">
    {$announcement.title}
    <div class="smallprint">
    {if $announcement.img}<img src="{$announcement.img}" width="16" height="16" alt="" class="listtype">{/if}
    {$announcement.subtitle}
    </div>
  </a>
</li>
{/foreach}
</ul>
{/if}