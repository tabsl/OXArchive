[{include file="headitem.tpl" title="SHOWLIST_TITLE"|oxmultilangassign box=" "}]

<script type="text/javascript">
<!--
function EditThis( sID)
{
    [{assign var="shMen" value=1}]

    [{foreach from=$menustructure item=menuholder }]
      [{if $shMen && $menuholder->nodeType == XML_ELEMENT_NODE && $menuholder->childNodes->length }]

        [{assign var="shMen" value=0}]
        [{assign var="mn" value=1}]

        [{foreach from=$menuholder->childNodes item=menuitem }]
          [{if $menuitem->nodeType == XML_ELEMENT_NODE && $menuitem->childNodes->length }]
            [{ if $menuitem->getAttribute('id') == 'mxorders' }]

              [{foreach from=$menuitem->childNodes item=submenuitem }]
                [{if $submenuitem->nodeType == XML_ELEMENT_NODE && $submenuitem->getAttribute('cl') == 'admin_order' }]

                    if ( top && top.navigation && top.navigation.adminnav ) {
                        var _sbli = top.navigation.adminnav.document.getElementById( 'nav-1-[{$mn}]-1' );
                        var _sba = _sbli.getElementsByTagName( 'a' );
                        top.navigation.adminnav._navAct( _sba[0] );
                    }

                [{/if}]
              [{/foreach}]

            [{ /if }]
            [{assign var="mn" value=$mn+1}]

          [{/if}]
        [{/foreach}]
      [{/if}]
    [{/foreach}]

    var oTransfer = document.getElementById("transfer");
    oTransfer.oxid.value=sID;
    oTransfer.cl.value='admin_order';
    oTransfer.submit();
}

function ChangeLanguage()
{
    var oList = document.getElementById("showlist");
    oList.language.value=oList.changelang.value;
    oList.editlanguage.value=oList.changelang.value;
    oList.submit();
}


//-->
</script>

<form name="transfer" id="transfer" action="[{ $shop->selflink }]" method="post">
    [{ $shop->hiddensid }]
    <input type="hidden" name="oxid" value="[{ $oxid }]">
    <input type="hidden" name="cl" value="">
    <input type="hidden" name="updatelist" value="1">
</form>

[{if $sql}]
    <span class="listitem">
    [{ oxmultilang ident="SHOWLIST_SQL" }] :[{$sql}]<br>
    [{ oxmultilang ident="SHOWLIST_CNT" }] : [{$resultcount}]<br>
    </span>
[{/if}]
[{ if $noresult }]
    <span class="listitem">
        <b>[{ oxmultilang ident="SHOWLIST_NORESULTS" }]</b><br><br>
    </span>
[{/if}]

<div id="liste">

<form name="showlist" id="showlist" action="[{ $shop->selflink }]" method="post">
    [{ $shop->hiddensid }]
    <input type="hidden" name="cl" value="list_order">
    <input type="hidden" name="sort" value="">

<table cellspacing="0" cellpadding="0" border="0" width="100%">
<tr>
    <td class="listfilter first">
        <div class="r1"><div class="b1">
        <input class="listedit" type="text" size="15" maxlength="128" name="where[oxorder.oxorderdate]" value="[{ $where->oxorder__oxorderdate|oxformdate }]"></td>
        </div></div>
    <td class="listfilter">
        <div class="r1"><div class="b1">
        <input class="listedit" type="text" size="15" maxlength="128" name="where[oxorderarticles.oxartnum]" value="[{ $where->oxorderarticles__oxartnum }]"></td>
        </div></div>
    <td class="listfilter">
        <div class="r1"><div class="b1">&nbsp;</div></div>
        </td>
    <td class="listfilter">
        <div class="r1"><div class="b1">
        <input class="listedit" type="text" size="15" maxlength="128" name="where[oxorderarticles.oxtitle]" value="[{ $where->oxorderarticles__oxtitle }]"></td>
        </div></div>
    <td class="listfilter" colspan="2">
        <div class="r1"><div class="b1">
        <div class="find"><input class="listedit" type="submit" name="submitit" value="[{ oxmultilang ident="GENERAL_SEARCH" }]"></div>
        </div></div>
    </td>
</tr>
<tr>
    <td class="listheader first"><a href="javascript:document.forms.showlist.sort.value='oxorder.oxorderdate';document.forms.showlist.submit();" class="listheader">[{ oxmultilang ident="snporderlistoxorderdate" }]</a></td>
    <td class="listheader"><a href="javascript:document.forms.showlist.sort.value='oxorderarticles.oxartnum';document.forms.showlist.submit();" class="listheader">[{ oxmultilang ident="snporderlistoxartnum" }]</a></td>
    <td class="listheader"><a href="javascript:document.forms.showlist.sort.value='oxorderamount';document.forms.showlist.submit();" class="listheader">[{ oxmultilang ident="snporderlistsum" }]</a></td>
    <td class="listheader"><a href="javascript:document.forms.showlist.sort.value='oxorderarticles.oxtitle';document.forms.showlist.submit();" class="listheader">[{ oxmultilang ident="snporderlistoxtitle" }]</a></td>
    <td class="listheader"><a href="javascript:document.forms.showlist.sort.value='oxprice';document.forms.showlist.submit();" class="listheader">[{ oxmultilang ident="price" }]</a></td>
</tr>

[{assign var="_cnt" value=0}]
[{foreach from=$mylist item=oOrder}]
    [{assign var="_cnt" value=$_cnt+1}]
    <tr id="row.[{$_cnt}]">

    <td class="listitem[{ $blWhite }]"><a href="Javascript:EditThis( '[{ $oOrder->oxorder__oxorderid->value }]');" class="listitem[{ $blWhite }]">[{ $oOrder->oxorder__oxorderdate|oxformdate }]</a></td>
    <td class="listitem[{ $blWhite }]"><a href="Javascript:EditThis( '[{ $oOrder->oxorder__oxorderid->value }]');" class="listitem[{ $blWhite }]">[{ $oOrder->oxorder__oxartnum->value }]</a></td>
    <td class="listitem[{ $blWhite }]"><a href="Javascript:EditThis( '[{ $oOrder->oxorder__oxorderid->value }]');" class="listitem[{ $blWhite }]">[{ $oOrder->oxorder__oxorderamount->value }]</a></td>
    <td class="listitem[{ $blWhite }]"><a href="Javascript:EditThis( '[{ $oOrder->oxorder__oxorderid->value }]');" class="listitem[{ $blWhite }]">[{ $oOrder->oxorder__oxtitle->getRawValue() }]</a></td>
    <td class="listitem[{ $blWhite }]"><a href="Javascript:EditThis( '[{ $oOrder->oxorder__oxorderid->value }]');" class="listitem[{ $blWhite }]">[{ $oOrder->oxorder__oxprice->value }]</a></td>
</tr>
[{if $blWhite == "2"}]
    [{assign var="blWhite" value=""}]
[{else}]
    [{assign var="blWhite" value="2"}]
[{/if}]
[{/foreach}]

</table>
</form>
[{ if $sumresult}]
<span class="listitem">
<b>[{ oxmultilang ident="SHOWLIST_SUM" }]:</b> [{ $sumresult}]<br>
</span>

</div>
[{/if}]
<script type="text/javascript">
if (parent.parent)
{   parent.parent.sShopTitle   = "[{$actshopobj->oxshops__oxname->getRawValue()|oxaddslashes}]";
    parent.parent.sMenuItem    = "";
    parent.parent.sMenuSubItem = "[{ oxmultilang ident="snporderlistheader" }]";
    parent.parent.sWorkArea    = "[{$_act}]";
    parent.parent.setTitle();
}
</script>
</body>
</html>
