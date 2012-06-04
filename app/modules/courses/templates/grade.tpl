{include file="findInclude:common/templates/header.tpl"}
<div class="nonfocal">
<h3>Assignment Name</h3>
<p>{$grade.title}</p>

{if $grade.dueDate}
<h3>Due Date</h3>
<p>{$grade.dueDate}</p>
{/if}

{if $grade.dateModified}
<h3>Last Submitted, Edited or Graded</h3>
<p>{$grade.dateModified}</p>
{/if}

{if $grade.grade !== null}
<h3>Grade</h3>
<p>{$grade.grade}</p>
{/if}

{if $grade.possiblePoints !== null}
<h3>Possible Points</h3>
<p>{$grade.possiblePoints}</p>
</div>
{/if}

{include file="findInclude:common/templates/footer.tpl"}
