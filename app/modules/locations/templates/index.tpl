{include file="findInclude:common/templates/header.tpl"}

{if isset($description) && strlen($description)}
  <p class="{block name='headingClass'}nonfocal smallprint{/block}">
    {$description|escape}
  </p>
{/if}

{include file="findInclude:common/templates/navlist.tpl" navlistItems=$locations navlistID="locations" subTitleNewline=true}

{include file="findInclude:common/templates/footer.tpl"}
