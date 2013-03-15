[{assign var="template_title" value="CUSTOMER_RIGHTOFWITHDRAWAL_TITLE"|oxmultilangassign }]
[{include file="_header.tpl" title=$template_title location=$template_title}]

<strong id="test_rightOfWithdrawalHeader" class="boxhead">[{ oxmultilang ident="CUSTOMER_RIGHTOFWITHDRAWAL" }]</strong>
<div class="box info">
  [{oxcontent ident="oxrightofwithdrawal"}]
</div>

[{insert name="oxid_tracker" title=$template_title}]
[{include file="_footer.tpl"}]
