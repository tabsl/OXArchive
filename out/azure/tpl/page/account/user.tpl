[{capture append="oxidBlock_content"}]
[{assign var="template_title" value="PAGE_ACCOUNT_USER_USERTITLE"|oxmultilangassign }]
<h1 id="addressSettingsHeader" class="pageHead">[{ $template_title }]</h1>
[{include file="form/user.tpl"}]
[{/capture}]
[{capture append="oxidBlock_sidebar"}]
	[{include file="page/account/inc/account_menu.tpl" active_link="billship"}]
[{/capture}]
[{include file="layout/page.tpl" sidebar="Left"}]