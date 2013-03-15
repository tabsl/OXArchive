[{if $payment->oxuserpayments__oxpaymentsid->value == "oxempty"}]
[{oxcontent ident="oxadminordernpplainemail"}]
[{else}]
[{oxcontent ident="oxadminorderplainemail"}]
[{/if}]

[{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_ORDERNOMBER" }] [{ $order->oxorder__oxordernr->value }]

[{ foreach from=$vouchers item=voucher}]
[{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_USEDCOUPONS" }] [{$voucher->oxmodvouchers__oxvouchernr->value}] - Nachlass [{$voucher->oxmodvouchers__oxdiscount->value}] [{ if $voucher->oxmodvouchers__oxdiscounttype->value == "absolute"}][{ $currency->name}][{else}]%[{/if}]
[{/foreach }]

[{foreach from=$basket->aBasketContents item=basketitem}]
[{ $basketitem->oProduct->oxarticles__oxtitle->value|strip_tags }][{ if $basketitem->oProduct->oxarticles__oxvarselect->value}], [{ $basketitem->oProduct->oxarticles__oxvarselect->value}][{/if}]
[{ if $basketitem->chosen_selectlist }][{foreach from=$basketitem->chosen_selectlist item=oList}][{ $oList->name }] [{ $oList->value }][{/foreach}][{/if}]
[{ if $basketitem->aPersParam }][{foreach key=sVar from=$basketitem->aPersParam item=aParam}][{$sVar}] : [{$aParam}][{/foreach}][{/if}]
[{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_WRAPPING" }] [{ if !$basketitem->wrapping }][{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_NONE" }][{else}][{$basketitem->oWrap->oxwrapping__oxname->value}][{/if}]
[{ if $basketitem->oProduct->oxarticles__oxorderinfo->value }][{ $basketitem->oProduct->oxarticles__oxorderinfo->value }][{/if}]
[{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_QUANTITY" }] [{$basketitem->dAmount}]
[{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_UNITPRICE" }] [{ $basketitem->oProduct->fprice }] [{ $currency->name}]
[{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_TOTAL" }] [{ $basketitem->ftotalprice }] [{ $currency->name}]
[{/foreach}]
------------------------------------------------------------------
[{ if !$basket->aDiscounts}]
[{* netto price *}]
[{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_TOTALNET" }] [{ $basket->fproductsnetprice }] [{ $currency->name}]
[{* VATs *}]
[{foreach from=$basket->aVATs item=VATitem key=key}]
[{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_PLUSTAX1" }] [{ $key }][{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_PLUSTAX2" }] [{ $VATitem }] [{ $currency->name}]
[{/foreach}]
[{/if}]
[{* brutto price *}]
[{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_TOTALGROSS" }] [{ $basket->fproductsprice }] [{ $currency->name}]
[{* applied discounts *}]
[{ if $basket->aDiscounts}]
  [{foreach from=$basket->aDiscounts item=oDiscount}]
  [{if $oDiscount->dDiscount < 0 }][{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_CHARGE" }][{else}][{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_DICOUNT" }][{/if}] [{ $oDiscount->sDiscount }]: [{if $oDiscount->dDiscount < 0 }][{ $oDiscount->fDiscount|replace:"-":"" }][{else}]-[{ $oDiscount->fDiscount }][{/if}] [{ $currency->name}]
  [{/foreach}]
  [{* netto price *}]
  [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_TOTALNET" }] [{ $basket->fproductsnetprice }] [{ $currency->name}]
  [{* VATs *}]
  [{foreach from=$basket->aVATs item=VATitem key=key}]
    [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_PLUSTAX1" }] [{ $key }][{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_PLUSTAX2" }] [{ $VATitem }] [{ $currency->name}]
  [{/foreach}]
[{/if}]
[{* voucher discounts *}]
[{if $basket->dVoucherDiscount }]
[{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_COUPON" }] [{ if $basket->fVoucherDiscount > 0 }]-[{/if}][{ $basket->fVoucherDiscount|replace:"-":"" }] [{ $currency->name}]
[{/if}]
[{* delivery costs *}]
[{* delivery VAT (if available) *}]
[{if $basket->dDelVAT > 0}]
[{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_SHIPPINGNET" }] [{ $basket->fdeliverynetcost }] [{ $currency->name}]
[{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_TAX1" }] [{ $basket->fDelVATPercent*100 }][{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_TAX2" }]  [{ $basket->fDelVAT }] [{ $currency->name}]
[{/if}]
[{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_SHIPPINGGROSS1" }] [{if $basket->dDelVAT > 0}][{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_SHIPPINGGROSS2" }] [{/if}]: [{ $basket->fdeliverycost }] [{ $currency->name}]
[{* payment sum *}]
[{ if $basket->dAddPaymentSum }]
[{if $basket->dAddPaymentSum >= 0}][{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_PAYMENTCHARGEDISCOUNT1" }][{else}][{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_PAYMENTCHARGEDISCOUNT2" }][{/if}] [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_PAYMENTCHARGEDISCOUNT3" }] [{ $basket->fAddPaymentNetSum }] [{ $currency->name}]
[{* payment sum VAT (if available) *}]
  [{ if $basket->dAddPaymentSumVAT }]
  [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_PAYMENTCHARGEVAT1" }] [{ $basket->fAddPaymentSumVATPercent}][{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_PAYMENTCHARGEVAT2" }] [{ $basket->fAddPaymentSumVAT }] [{ $currency->name}]
  [{/if}]
[{/if}]

[{ if $basket->dWrappingPrice }]
  [{if $basket->fWrappingVAT}]
    [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_WRAPPINGNET" }] [{ $basket->fWrappingNetto }] [{ $currency->name}]
    [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_PLUSTAX21" }] [{ $basket->fWrappingVATPercent }][{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_PLUSTAX22" }] [{ $basket->fWrappingVAT }] [{ $currency->name}]
  [{/if}]
    [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_WRAPPINGANDGREETINGCARD1" }][{if $basket->fWrappingVAT}] [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_WRAPPINGANDGREETINGCARD2" }][{/if}]: [{ $basket->fWrappingPrice }] [{ $currency->name}]
[{/if}]

[{* grand total price *}]
[{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_GRANDTOTAL" }] [{ $basket->fprice }] [{ $currency->name}]
[{if $basket->oCard }]
    [{ oxmultilang ident="EMAIL_ORDER_OWNER_HTML_ATENTIONGREETINGCARD" }]
    [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_YOURMESSAGE" }]
    [{$basket->giftmessage}]
[{/if}]

[{ if $order->oxorder__oxremark->value }]
[{ oxmultilang ident="EMAIL_ORDER_OWNER_HTML_MESSAGE" }] [{ $order->oxorder__oxremark->value }]
[{/if}]

[{if $payment->oxuserpayments__oxpaymentsid->value != "oxempty"}][{ oxmultilang ident="EMAIL_ORDER_OWNER_HTML_PAYMENTINFO" }]
[{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_PAYMENTMETHOD" }][{ $payment->oxpayments__oxdesc->value }]
[{ oxmultilang ident="EMAIL_ORDER_OWNER_HTML_PAYMENTINFOOFF" }]
[{ * foreach from=$payment->aDynValues item=value *}]
[{ * $value->name * }] : [{ * $value->value *}]
[{* /foreach *}]
[{/if}]

[{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_EMAILADDRESS" }] [{ $user->oxuser__oxusername->value }]

[{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_BILLINGADDRESS" }]
[{ $order->oxorder__oxbillcompany->value }]
[{ $order->oxorder__oxbillsal->value }] [{ $order->oxorder__oxbillfname->value }] [{ $order->oxorder__oxbilllname->value }]
[{if $order->oxorder__oxbilladdinfo->value }][{ $order->oxorder__oxbilladdinfo->value }][{/if}]
[{ $order->oxorder__oxbillstreet->value }] [{ $order->oxorder__oxbillstreetnr->value }]
[{ $order->oxorder__oxbillzip->value }] [{ $order->oxorder__oxbillcity->value }]
[{ $order->oxorder__oxbillcountry->value }]
[{if $order->oxorder__oxbillustid->value}][{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_VATIDNOMBER" }] [{ $order->oxorder__oxbillustid->value }][{/if}]
[{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_PHONE" }] [{ $order->oxorder__oxbillfon->value }]

[{ if $order->oxorder__oxdellname->value }][{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_SHIPPINGADDRESS" }]
[{ $order->oxorder__oxdelcompany->value }]
[{ $order->oxorder__oxdelsal->value }] [{ $order->oxorder__oxdelfname->value }] [{ $order->oxorder__oxdellname->value }]
[{if $order->oxorder__oxdeladdinfo->value }][{ $order->oxorder__oxdeladdinfo->value }][{/if}]
[{ $order->oxorder__oxdelstreet->value }] [{ $order->oxorder__oxdelstreetnr->value }]
[{ $order->oxorder__oxdelzip->value }] [{ $order->oxorder__oxdelcity->value }]
[{ $order->oxorder__oxdelcountry->value }]
[{/if}]

[{if $payment->oxuserpayments__oxpaymentsid->value != "oxempty"}][{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_SHIPPINGCARRIER" }] [{ $order->oDelSet->oxdeliveryset__oxtitle->value }]
[{/if}]

[{ oxcontent ident="oxemailfooterplain" }]
