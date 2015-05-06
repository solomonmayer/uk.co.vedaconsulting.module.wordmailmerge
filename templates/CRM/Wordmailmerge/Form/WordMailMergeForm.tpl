{* HEADER *}


{* FIELD EXAMPLE: OPTION 1 (AUTOMATIC LAYOUT) *}
<div class="messages status no-popup">{include file="CRM/Contact/Form/Task.tpl"}</div>
{foreach from=$elementNames item=elementName}
  <div class="crm-section">
    <div class="label">{$form.$elementName.label}</div>
    <div class="content">{$form.$elementName.html}</div>
    <div class="clear"></div>
  </div>
{/foreach}

{* FIELD EXAMPLE: OPTION 2 (MANUAL LAYOUT)

  <div>
    <span>{$form.favorite_color.label}</span>
    <span>{$form.favorite_color.html}</span>
  </div>

{* FOOTER *}
<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>

<div id="Event" class="boxBlock">
  <table class="report-layout">
    <tbody>
      <tr id="row_1" class="crm-report-instanceList">
        <td><strong>Available Tokens</strong></td>
        {foreach from=$availableTokens key=tokenKey item=tokenValue}
      </tr>
      <tr id="row_2" class="crm-report-instanceList">
        <td>{$tokenValue.text}</td>
        <td>[CiviCRM. {$tokenValue.token_name};block=w:tr]</td>
      </tr>
    {/foreach}
    </tbody>
  </table>
</div>