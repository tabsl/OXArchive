[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]
<script type="text/javascript">
<!--
[{ if $updatelist == 1}]
    UpdateList('[{ $oxid }]');
[{ /if}]

function UpdateList( sID)
{
    var oSearch = parent.list.document.getElementById("search");
    oSearch.oxid.value=sID;
    oSearch.submit();
}
//-->
</script>
<form name="transfer" id="transfer" action="[{ $shop->selflink }]" method="post">
    [{ $shop->hiddensid }]
    <input type="hidden" name="oxid" value="[{ $oxid }]">
    <input type="hidden" name="cl" value="newsletter_main">
    <input type="hidden" name="editlanguage" value="[{ $editlanguage }]">
</form>


        <form name="myedit" id="myedit" action="[{ $shop->selflink }]" method="post" onSubmit="copyLongDesc( 'oxnewsletter__oxtemplate' );">
        [{ $shop->hiddensid }]
        <input type="hidden" name="cl" value="newsletter_main">
        <input type="hidden" name="fnc" value="">
        <input type="hidden" name="oxid" value="[{ $oxid }]">
        <input type="hidden" name="editval[oxnewsletter__oxid]" value="[{ $oxid }]">
        <input type="hidden" name="editval[oxnewsletter__oxtemplate]" value="">

            <table cellspacing="0" cellpadding="0" border="0" width="98%;">
              <tr>
                <td class="edittext" width="60">
                [{ oxmultilang ident="GENERAL_TITLE" }]
                </td>
                <td class="edittext">
                <input type="text" class="editinput" style="width:100%" size="120" maxlength="[{$edit->oxnewsletter__oxtitle->fldmax_length}]" name="editval[oxnewsletter__oxtitle]" value="[{$edit->oxnewsletter__oxtitle->value}]">
                </td>
              </tr>
              <tr>
                <td class="edittext" style="width:60px" valign="top">
                [{ oxmultilang ident="NEWSLETTER_MAIN_MODEL" }]
                </td>
                <td valign="top" class="edittext" align="left">

                        [{ $editor }]


                </td>
              </tr>
              <tr>
                <td class="edittext">
                </td>
                <td class="edittext"><br>
                <input type="submit" class="edittext" name="save" value="[{ oxmultilang ident="GENERAL_SAVE" }]" onClick="Javascript:document.myedit.fnc.value='save'"">
                </td>
              </tr>
            </table>

  </form>
[{include file="bottomnaviitem.tpl"}]

[{include file="bottomitem.tpl"}]
