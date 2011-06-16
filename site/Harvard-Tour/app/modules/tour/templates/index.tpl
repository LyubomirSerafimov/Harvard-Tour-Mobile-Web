{capture name="customHeader" assign="customHeader"}
  <div id="welcomehead">
    {if $resumeURL}
      <a id="resumelink" href="{$resumeURL}">
        <img src="/modules/tour/images/logo@2x.png" alt="harvard yard tour" width="233" height="33" border="0" />
        <br/>
        <img id="resume" src="/modules/tour/images/resume@2x.png" alt="resume your tour" width="131" height="30" border="0" />
      </a>
      <a id="startoverlink" href="{$startURL}">
        <img id="startover" src="/modules/tour/images/startover@2x.png" alt="start a new tour" width="131" height="30" border="0" />
      </a>
      
    {else}
      <a id="startlink" href="{$startURL}">
        <img id="logo" src="/modules/tour/images/logo@2x.png" alt="harvard yard tour" width="233" height="33" border="0" />
        <br/>
        <img id="begin" src="/modules/tour/images/begin@2x.png" alt="begin your tour" width="131" height="30" border="0" />
      </a>
    {/if}
  </div>
  <div id="hero">
    <img src="/modules/tour/images/hero-hdpi.jpg" alt="Photo of Harvard" width="100%" border="0" />
    {if $pagetype == 'compliant' && $platform == 'iphone'}
      <div id="download">
        <a href="">
          Download the free iPhone app
          <img src="/modules/tour/images/iphone4@2x.png" alt="iPhone" width="25" height="47" border="0" />
        </a>
      </div>
    {/if}
  </div>
{/capture}

{include file="findInclude:common/templates/header.tpl" customHeader=$customHeader}

{include file="findInclude:modules/tour/templates/include/pagecontents.tpl" pageContents=$contents}

{include file="findInclude:common/templates/footer.tpl"}
