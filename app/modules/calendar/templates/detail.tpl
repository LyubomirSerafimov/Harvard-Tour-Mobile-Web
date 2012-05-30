{include file="findInclude:common/templates/header.tpl"}

{block name="title"}
<div class="nonfocal">
  <h2>{$title}</h2>
  <p class="smallprint">{$date}</p>
</div>
{/block}
  
{block name="fields"}
{if count($fields)}
  {include file="findInclude:common/templates/navlist.tpl" navlistItems=$fields accessKey=false}
{/if}
{/block}

{include file="findInclude:common/templates/footer.tpl"}
