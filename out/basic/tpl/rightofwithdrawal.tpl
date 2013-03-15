[{assign var="template_title" value="RIGHTOFWITHDRAWAL"|oxmultilangassign }]
[{include file="_header.tpl" title=$template_title location=$template_title}]

<strong class="boxhead">[{ oxmultilang ident="RIGHTOFWITHDRAWAL" }]</strong>
<div class="box info">
      [{ oxcontent ident="oxrightofwithdrawal" }]
</div>

[{insert name="oxid_tracker" title=$template_title }]
[{include file="_footer.tpl" }]
