[{assign var="template_title" value="DELIVERY_INFO_TITLE"|oxmultilangassign }]
[{include file="_header.tpl" title=$template_title location=$template_title}]

<strong id="test_deliveryHeader" class="boxhead">[{$template_title}]</strong>
<div class="box info">
  [{ oxcontent ident="oxdeliveryinfo" }]
</div>

[{ insert name="oxid_tracker" title=$template_title }]
[{include file="_footer.tpl"}]
