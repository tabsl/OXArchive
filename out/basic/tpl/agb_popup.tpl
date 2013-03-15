  [{assign var="template_title" value="AGB_POPUP_AGB"|oxmultilangassign }]
  [{include file="_header_plain.tpl" title=$template_title location=$template_title}]

  <strong class="boxhead">[{ oxmultilang ident="AGB_POPUP_AGB" }]</strong>
  <div class="box info">
      [{ oxcontent ident="oxagb" }]
  </div>

  [{insert name="oxid_tracker" title=$template_title }]
  [{include file="_footer_plain.tpl"}]

