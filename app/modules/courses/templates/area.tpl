{include file="findInclude:common/templates/header.tpl"}

{if $areas}
{block name="areas"}
  {include file="findInclude:common/templates/navlist.tpl" navlistItems=$areas}
{/block}
{/if}

{if $courses}
{block name="courses"}
  {include file="findInclude:common/templates/navlist.tpl" navlistItems=$courses}
{/block}
{/if}

{include file="findInclude:common/templates/footer.tpl"}
