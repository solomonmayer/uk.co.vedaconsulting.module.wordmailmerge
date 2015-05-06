{* template block that contains the new field *}
  {*<div>Attach File:{$form.uploadFile.html}{$form.attachDesc.html} </div>*}
  {if $action eq 1 || $action eq 2}
  <div class="crm-msg-template-form-block-attachment">
     {include file="CRM/Form/attachment.tpl"}
 </div>
 {/if} 
{* reposition the above block after #someOtherBlock *}
<script type="text/javascript">
  cj('.crm-msg-template-form-block-attachment').insertAfter(cj("#pdf_format_id").parent().parent().parent());
</script>