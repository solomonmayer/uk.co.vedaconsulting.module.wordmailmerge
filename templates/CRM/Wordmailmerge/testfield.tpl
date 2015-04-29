{* template block that contains the new field *}
<div id="testfield-tr">
  <div>Attach File:{$form.uploadFile.html}{$form.attachDesc.html} </div>
  {*<table class="crm-info-panel">
    {if $currentAttachmentInfo}
       {include file="CRM/Form/attachment.tpl"}
    {/if}
  </table>*}
  
</div>
{* reposition the above block after #someOtherBlock *}
<script type="text/javascript">
  cj('#testfield-tr').insertAfter('#pdf_format_id')
</script>
