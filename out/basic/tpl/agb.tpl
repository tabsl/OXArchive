[{assign var="template_title" value="AGB_AGB"|oxmultilangassign }]
[{include file="_header.tpl" title=$template_title location=$template_title}]

<strong id="test_agbHeader" class="boxhead">[{ oxmultilang ident="AGB_AGB" }]</strong>
<div class="box info">
  [{ oxcontent ident="oxagb" }]
</div>

[{insert name="oxid_tracker" title=$template_title }]
[{include file="_footer.tpl" }]
