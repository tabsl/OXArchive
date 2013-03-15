[{assign var="template_title" value="FORGOTPWD_TITLE"|oxmultilangassign}]
[{include file="_header.tpl" title=$template_title location=$template_title}]

<strong class="boxhead">[{$template_title}]</strong>
<div class="box info">
  [{ oxmultilang ident="FORGOTPWD_FORGOTPWD" }] <br>
  [{ oxmultilang ident="FORGOTPWD_WEWILLSENDITTOYOU" }]<br><br>
    <form action="[{ $oViewConf->getSelfActionLink() }]" name="order" method="post">
      <div>
          [{ $oViewConf->getHiddenSid() }]
          <input type="hidden" name="fnc" value="forgotpassword">
          <input type="hidden" name="cl" value="forgotpwd">
          <input type="hidden" name="cnid" value="[{$oViewConf->getActCatId()}]">
      </div>
      <table class="form">
          <tr>
            <td><label>[{ oxmultilang ident="FORGOTPWD_YOUREMAIL" }]</label></td>
            <td><input id="test_lgn_usr" type="text" name="lgn_usr" value="[{$lgn_usr}]" size="45" ></td>
          </tr>
          [{ if $oView->getForgotEmail()}]
            <tr>
              <td></td>
              <td>[{ oxmultilang ident="FORGOTPWD_PWDWASSEND" }] [{$oView->getForgotEmail()}]</td>
            </tr>
          [{ /if}]
          <tr>
            <td></td>
            <td><span class="btn"><input type="submit" name="save" value="[{ oxmultilang ident="FORGOTPWD_REQUESTPWD" }]" class="btn"></span></td>
          </tr>
      </table>
    </form>
  [{ oxmultilang ident="FORGOTPWD_AFTERCLICK" }]<br>
  <br>
  [{ oxcontent ident="oxforgotpwd" }]
</div>

[{ insert name="oxid_tracker" title=$template_title }]
[{include file="_footer.tpl" }]
