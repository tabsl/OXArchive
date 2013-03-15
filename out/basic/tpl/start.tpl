[{include file="_header.tpl" title=$template_title location="START_TITLE"|oxmultilangassign isStart=true}]

[{if $oView->isDemoShop()}]
    [{include file="inc/admin_banner.tpl"}]
[{/if}]

<div class="wellcome">
    [{ oxcontent ident="oxstartwelcome" }]
</div>

[{if $oView->getTopArticleList() }]
  [{foreach from=$oView->getTopArticleList() item=actionproduct name=WeekArt}]
    [{include file="inc/product.tpl" product=$actionproduct head="START_WEEKSPECIAL"|oxmultilangassign testid="WeekSpecial_"|cat:$actionproduct->oxarticles__oxid->value testHeader="WeekSpecial_`$smarty.foreach.WeekArt.iteration`"}]
  [{/foreach}]
[{/if}]

[{if $oView->getFirstArticle() }]
  [{oxcontent ident="oxfirststart" field="oxtitle" assign="oxfirststart_title"}]
  [{oxcontent ident="oxfirststart" assign="oxfirststart_text"}]
  [{assign var="firstarticle" value=$oView->getFirstArticle()}]
  [{include file="inc/product.tpl" size='big' class='topshop' head=$oxfirststart_title head_desc=$oxfirststart_text product=$firstarticle testid="FirstArticle_"|cat:$firstarticle->oxarticles__oxid->value testHeader=FirstArticle}]
[{/if}]

[{if ($oView->getArticleList()|@count)>0 }]
  <strong id="test_LongRunHeader" class="head2">[{ oxmultilang ident="START_LONGRUNNINGHITS"}]</strong>
  [{if ($oView->getArticleList()|@count) is not even  }][{assign var="actionproduct_size" value="big"}][{/if}]
  [{foreach from=$oView->getArticleList() item=actionproduct}]
      [{include file="inc/product.tpl" product=$actionproduct size=$actionproduct_size testid="LongRun_"|cat:$actionproduct->oxarticles__oxid->value }]
      [{assign var="actionproduct_size" value=""}]
  [{/foreach}]
[{/if}]

[{if ($oView->getNewestArticles()|@count)>0 }]
  <strong id="test_FreshInHeader" class="head2">
    [{ oxmultilang ident="START_JUSTARRIVED"}]

    [{if $rsslinks.newestArticles}]
        <a class="rss" id="rss.newestArticles" href="[{$rsslinks.newestArticles.link}]" title="[{$rsslinks.newestArticles.title}]"></a>
        [{oxscript add="oxid.blank('rss.newestArticles');"}]
    [{/if}]
  </strong>
  [{foreach from=$oView->getNewestArticles() item=actionproduct}]
      [{include file="inc/product.tpl" product=$actionproduct size="small" testid="FreshIn_"|cat:$actionproduct->oxarticles__oxid->value}]
  [{/foreach}]
[{/if}]

[{if ($oView->getCatOfferArticleList()|@count)>0 }]
  <strong id="test_CategoriesHeader" class="head2">[{ oxmultilang ident="START_CATEGORIES"}]</strong>
  [{if ($oView->getCatOfferArticleList()|@count) is not even  }][{assign var="actionproduct_size" value="big"}][{/if}]
  [{foreach from=$oView->getCatOfferArticleList() item=actionproduct name=CatArt}]
      [{assign var="actionproduct_title" value=$actionproduct->oCategory->oxcategories__oxtitle->value}]
      [{if $actionproduct->oCategory->getNrOfArticles() > 0}][{assign var="actionproduct_title" value=$actionproduct_title|cat:" ("|cat:$actionproduct->oCategory->getNrOfArticles()|cat:")"}][{/if}]
      [{include file="inc/product.tpl" product=$actionproduct size=$actionproduct_size head=$actionproduct_title head_link=$actionproduct->oCategory->getLink() testid="CatArticle_"|cat:$actionproduct->oxarticles__oxid->value  testHeader="Category_`$smarty.foreach.CatArt.iteration`"}]
      [{assign var="actionproduct_size" value=""}]
  [{/foreach}]
[{/if}]

[{include file="inc/tags.tpl"}]

[{ insert name="oxid_tracker" title=$template_title }]
[{include file="_footer.tpl" }]