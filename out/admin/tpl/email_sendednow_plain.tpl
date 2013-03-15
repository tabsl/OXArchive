[{ oxmultilang ident="EMAIL_SENDEDNOW_HTML_HY" }] [{ $order->oxorder__oxbillsal->value }] [{ $order->oxorder__oxbillfname->value }] [{ $order->oxorder__oxbilllname->value }],

[{ oxmultilang ident="EMAIL_SENDEDNOW_HTML_SENDEDITEMS" }]

[{ oxmultilang ident="EMAIL_SENDEDNOW_HTML_ORDERGOSTO" }]:

[{ if $order->oxorder__oxdellname->value }] 
	[{ $order->oxorder__oxdelfname->value }] [{ $order->oxorder__oxdellname->value }]
	[{ $order->oxorder__oxdelstreet->value }] [{ $order->oxorder__oxdelstreetnr->value }]
	[{ $order->oxorder__oxdelzip->value }] [{ $order->oxorder__oxdelcity->value }]
[{else}]
	[{ $order->oxorder__oxbillfname->value }] [{ $order->oxorder__oxbilllname->value }]
	[{ $order->oxorder__oxbillstreet->value }] [{ $order->oxorder__oxbillstreetnr->value }]
	[{ $order->oxorder__oxbillzip->value }] [{ $order->oxorder__oxbillcity->value }]
[{/if}]

[{ oxmultilang ident="EMAIL_SENDEDNOW_HTML_ORDERNUM" }] : [{ $order->oxorder__oxordernr->value }]

[{foreach from=$order->getOrderArticles() item=oOrderArticle}]  
[{ $oOrderArticle->oxorderarticles__oxamount->value }] [{ $oOrderArticle->oxorderarticles__oxtitle->value }] [{ $oOrderArticle->oxorderarticles__oxselvariant->value }]
[{/foreach}] 

[{ oxmultilang ident="EMAIL_PRICEALARM_CUSTOMER_TEAM1" }] [{ $shop->oxshops__oxname->value }] [{ oxmultilang ident="EMAIL_PRICEALARM_CUSTOMER_TEAM2" }]

[{ oxcontent ident="oxemailfooterplain" }]
