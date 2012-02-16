{capture name="categorySelect" assign="categorySelect"}
  <select class="newsinput" id="section" name="section" onchange="loadSection(this);">
    {foreach $sections as $section}
      {if $section['selected']}
        <option value="{$section['value']}" selected="true">{$section['title']|escape}</option>
      {else}
        <option value="{$section['value']}">{$section['title']|escape}</option>
      {/if}
    {/foreach}
  </select>
{/capture}

{if $loggedIn|default: false}
    {if count($sections) > 1}
        <div class="header">
          <div id="category-switcher" class="category-mode">
            <form method="get" action="index" id="category-form">
              <table border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td class="formlabel">{"TERM_TEXT"|getLocalizedString}</td>
                  <td class="inputfield"><div id="news-category-select">{$categorySelect}</div></td>
                </tr>
              </table>
              {foreach $hiddenArgs as $arg => $value}
                <input type="hidden" name="{$arg}" value="{$value}" />
              {/foreach}
              {foreach $breadcrumbSamePageArgs as $arg => $value}
                <input type="hidden" name="{$arg}" value="{$value}" />
              {/foreach}
            </form>
          </div>
        </div>
    {elseif $termTitle}
        <div class="nonfocal"><h3>{$termTitle}</h3></div>
    {/if}
{/if}