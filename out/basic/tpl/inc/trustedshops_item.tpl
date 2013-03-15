<form name="formSiegel" method="get" action="http://www.trustedshops.de/shop-info/oxid/" id="formSiegel">
  <div>
      <input type="image" src="[{$oViewConf->getImageUrl()}]/trustedshops_[{$oViewConf->getActLanguageId()}].gif" alt="[{ oxmultilang ident="INC_TRUSTEDSHOPS_ITEM_SEALOFAPPROVAL" }]">
      <input name="shop_id" type="hidden" value="[{$oView->getTrustedShopId()}]">
  </div>
</form>
[{oxscript add="oxid.blank('formSiegel');"}]