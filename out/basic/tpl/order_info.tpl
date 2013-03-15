[{assign var="template_title" value="INC_INFOBOX_HOWTOORDER"|oxmultilangassign}]
[{include file="_header.tpl" title=$template_title location=$template_title}]

<strong id="test_howToOrderHeader" class="boxhead">[{$template_title}]</strong>
<div class="box info">
  [{ oxcontent ident="oxorderinfo" }]
</div>

[{ insert name="oxid_tracker" title=$template_title }]
[{include file="_footer.tpl"}]
