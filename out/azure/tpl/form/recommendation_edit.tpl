<form action="[{ $oViewConf->getSelfActionLink() }]" name="saverecommlist" method="post">
    <div class="listmaniaAdd clear">
        [{ $oViewConf->getHiddenSid() }]
        [{ $oViewConf->getNavFormParams() }]
        <input type="hidden" name="fnc" value="saveRecommList">
        <input type="hidden" name="cl" value="account_recommlist">
        [{if $actvrecommlist}]
            <input type="hidden" name="recommid" value="[{$actvrecommlist->getId()}]">
        [{/if}]
        [{if $actvrecommlist && $oView->isSavedList()}]
            [{ oxmultilang ident="PAGE_RECOMMENDATIONS_EDIT_LISTSAVED" }]
        [{/if}]
        <ul class="form clear">
            <li>
                <label>[{ oxmultilang ident="FORM_RECOMMENDATION_EDIT_LISTTITLE" }]:<span class="req">*</span></label>
                <input type="text" name="recomm_title" size=73 maxlength=73 value="[{$actvrecommlist->oxrecommlists__oxtitle->value}]" >
            </li>
            <li>
                <label>[{ oxmultilang ident="FORM_RECOMMENDATION_EDIT_LISTAUTHOR" }]:</label>
                <input type="text" name="recomm_author" size=73 maxlength=73 value="[{if $actvrecommlist->oxrecommlists__oxauthor->value}][{$actvrecommlist->oxrecommlists__oxauthor->value}][{elseif !$actvrecommlist}][{ $oxcmp_user->oxuser__oxfname->value }] [{ $oxcmp_user->oxuser__oxlname->value }][{/if}]" >
            </li>
            <li>
                <label>[{ oxmultilang ident="FORM_RECOMMENDATION_EDIT_LISTDESC" }]:</label>
                <textarea class="areabox" cols="70" rows="8" name="recomm_desc" >[{$actvrecommlist->oxrecommlists__oxdesc->value}]</textarea>
            </li>
            <li class="formSubmit">
                <button class="submitButton" type="submit">[{ oxmultilang ident="FORM_RECOMMENDATION_EDIT_SAVE" }]</button>
            </li>
        </ul>
    </div>
</form>
