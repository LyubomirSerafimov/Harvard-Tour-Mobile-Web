{include file="findInclude:common/templates/header.tpl"}

{block name="browseHeader"}{/block}
{block name="browseList"}
{if $browseLinks}
{include file="findInclude:modules/courses/templates/include/updatesList.tpl" updates=$browseLinks}
{else}
{"NO_CONTENT"|getLocalizedString}
{/if}
{/block}
{block name="browseFooter"}{/block}

{include file="findInclude:common/templates/footer.tpl"}
