[{if $tree || $oContent}]
[{defun name="category_tree" tree=$tree act=$act class=$class issub=false testSubCat=''}]
[{strip}]
    <ul [{if $class}]class="[{$class}]"[{/if}]>
    [{foreach from=$tree item=ocat key=catkey name=$test_catName}]
        [{if $ocat->getContentCats() }]
            [{foreach from=$ocat->getContentCats() item=ocont key=contkey name=cont}]
            <li><a href="[{$ocont->getLink()}]" class="[{if !$issub}]root[{/if}][{if isset($act) && $act->getId()==$ocont->getId()}] act[{/if}]">[{ $ocont->oxcontents__oxtitle->value }]</a></li>
            [{/foreach}]
        [{/if}]
        [{if $ocat->getIsVisible() }]
        <li>
            <a id="test_BoxLeft_Cat_[{if !$issub}][{$ocat->oxcategories__oxid->value}]_[{$smarty.foreach.$test_catName.iteration}][{else}][{$testSubCat}]_sub[{$smarty.foreach.$test_catName.iteration}][{/if}]" href="[{$ocat->getLink()}]" class="[{if !$issub}]root [{/if}][{if $ocat->hasVisibleSubCats}][{if $ocat->expanded }]exp [{/if}]has [{/if}][{if isset($act) && $act->getId()==$ocat->getId()}] act[{/if}]">[{$ocat->oxcategories__oxtitle->value}] [{if $ocat->getNrOfArticles() > 0}] ([{$ocat->getNrOfArticles()}])[{/if}]</a>
            [{if $ocat->getSubCats() && $ocat->expanded}]
                [{fun name="category_tree" tree=$ocat->getSubCats() act=$act class="" issub=true testSubCat=$ocat->oxcategories__oxid->value }]
            [{/if}]
        </li>
        [{/if}]
    [{foreachelse}]
        [{if $oContent}]
            <li><a  href="[{$oContent->getLink()}]">[{ $oContent->oxcontents__oxtitle->value }]</a></li>
        [{/if}]
    [{/foreach}]
    </ul>
[{/strip}]
[{/defun}]
[{/if}]