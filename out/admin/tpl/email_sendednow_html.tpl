<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=[{$charset}]">
<title>[{ $shop->oxshops__oxsendednowsubject->value }]</title>
</head>
<body bgcolor="#FFFFFF" link="#355222" alink="#355222" vlink="#355222" style="font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; font-size: 10px;">
<br>
[{ oxmultilang ident="EMAIL_SENDEDNOW_HTML_HY" }], [{ $order->oxorder__oxbillsal->value }] [{ $order->oxorder__oxbillfname->value }] [{ $order->oxorder__oxbilllname->value }],<br>
<br>
[{ oxmultilang ident="EMAIL_SENDEDNOW_HTML_SENDEDITEMS" }]<br>
<br>
[{ oxmultilang ident="EMAIL_SENDEDNOW_HTML_ORDERGOSTO" }]:<br>
<br>
[{ if $order->oxorder__oxdellname->value }]
    [{ $order->oxorder__oxdelfname->value }] [{ $order->oxorder__oxdellname->value }]<br>
    [{ $order->oxorder__oxdelstreet->value }] [{ $order->oxorder__oxdelstreetnr->value }]<br>
    [{ $order->oxorder__oxdelzip->value }] [{ $order->oxorder__oxdelcity->value }]<br>
[{else}]
    [{ $order->oxorder__oxbillfname->value }] [{ $order->oxorder__oxbilllname->value }]<br>
    [{ $order->oxorder__oxbillstreet->value }] [{ $order->oxorder__oxbillstreetnr->value }]<br>
    [{ $order->oxorder__oxbillzip->value }] [{ $order->oxorder__oxbillcity->value }]<br>
[{/if}]
<br>
<b>[{ oxmultilang ident="EMAIL_SENDEDNOW_HTML_ORDERNUM" }] : [{ $order->oxorder__oxordernr->value }]</b><br>
<br>
<table border="0" cellspacing="0" cellpadding="0">
<tr>
    <td style="font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; font-size: 10px; background-color: #494949; color: #FFFFFF;" align="right" width="70">
    <b>[{ oxmultilang ident="GENERAL_SUM" }]</b>
    </td>
    <td style="font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; font-size: 10px; background-color: #494949; color: #FFFFFF;" height="15" width="100">
    &nbsp;&nbsp;<b>[{ oxmultilang ident="EMAIL_SENDEDNOW_HTML_PRODUCT" }]</b>
    </td>
    <td style="font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; font-size: 10px; background-color: #494949; color: #FFFFFF;" align="right" width="150">
    [{ oxmultilang ident="EMAIL_SENDEDNOW_HTML_PRODUCTRATING" }]
    </td>
</tr>
[{foreach from=$order->getOrderArticles() item=oOrderArticle}]
<tr>
        <td style="font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; font-size: 10px; padding-top: 10px;" valign="top" align="right">
            [{ $oOrderArticle->oxorderarticles__oxamount->value }]
        </td>
        <td valign="top" style="font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; font-size: 10px; padding-top: 10px;">
            [{ $oOrderArticle->oxorderarticles__oxtitle->value }] [{ $oOrderArticle->oxorderarticles__oxselvariant->value }]
        </td>
        <td style="font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; font-size: 10px; padding-top: 10px;" valign="top" align="right">
            <a href="[{$shop->basedir}]index.php?anid=[{ $oOrderArticle->oxorderarticles__oxartid->value }]&cl=review" style="font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; font-size: 10px;" target="_new">[{ oxmultilang ident="EMAIL_SENDEDNOW_HTML_RATE" }]</a>
        </td>
</tr>
[{/foreach}]
</table>
<br>
<br>
[{ oxmultilang ident="EMAIL_PRICEALARM_CUSTOMER_TEAM1" }] [{ $shop->oxshops__oxname->value }] [{ oxmultilang ident="EMAIL_PRICEALARM_CUSTOMER_TEAM2" }]<br>
<br><br>
[{ oxcontent ident="oxemailfooter" }]
</body>
</html>
