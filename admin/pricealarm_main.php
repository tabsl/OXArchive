<?php
/**
 *    This file is part of OXID eShop Community Edition.
 *
 *    OXID eShop Community Edition is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    OXID eShop Community Edition is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with OXID eShop Community Edition.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link http://www.oxid-esales.com
 * @package admin
 * @copyright © OXID eSales AG 2003-2009
 * $Id: pricealarm_main.php 14016 2008-11-06 13:31:20Z arvydas $
 */

/**
 * Admin article main pricealarm manager.
 * Performs collection and updatind (on user submit) main item information.
 * Admin Menu: Customer News -> pricealarm -> Main.
 * @package admin
 */
class PriceAlarm_Main extends oxAdminDetails
{
    /**
     * Executes parent method parent::render(), creates oxpricealarm object
     * and passes it's data to Smarty engine. Returns name of template file
     * "pricealarm_main.tpl".
     *
     * @return string
     */
    public function render()
    {   $myConfig = $this->getConfig();

            // #1140 R - price must be checked from the object.
            $sql = "select oxarticles.oxid, oxpricealarm.oxprice from oxpricealarm, oxarticles where oxarticles.oxid = oxpricealarm.oxartid and oxpricealarm.oxsended = '000-00-00 00:00:00'";
            $rs = oxDb::getDb()->Execute( $sql);
            $iAllCnt_counting = 0;
            if ($rs != false && $rs->recordCount() > 0) {
                while (!$rs->EOF) {
                    $oArticle = oxNew("oxarticle" );
                    $oArticle->load($rs->fields[0]);
                    if ($oArticle->getPrice()->getBruttoPrice() <= $rs->fields[1])
                        $iAllCnt_counting++;
                    $rs->moveNext();
                }
            }
            $this->_aViewData['iAllCnt'] = $iAllCnt_counting;

        $soxId = oxConfig::getParameter( "oxid");
        // check if we right now saved a new entry
        $sSavedID = oxConfig::getParameter( "saved_oxid");
        if ( ($soxId == "-1" || !isset( $soxId)) && isset( $sSavedID) ) {
            $soxId = $sSavedID;
            oxSession::deleteVar( "saved_oxid");
            $this->_aViewData["oxid"] =  $soxId;
            // for reloading upper frame
            $this->_aViewData["updatelist"] =  "1";
        }

        if ( $soxId != "-1" && isset( $soxId)) {
            // load object
            $oPricealarm = oxNew( "oxpricealarm" );
            $oPricealarm->load( $soxId);

            $oDefCurr = $myConfig->getActShopCurrencyObject();
            $oArticle = oxNew( "oxarticle" );
            $oArticle->load($oPricealarm->oxpricealarm__oxartid->value);

            if ( $oArticle->oxarticles__oxparentid->value && !$oArticle->oxarticles__oxtitle->value) {
                $oParent = oxNew( "oxarticle" );
                $oParent->load($oArticle->oxarticles__oxparentid->value);
                $oArticle->oxarticles__oxtitle->setValue($oParent->oxarticles__oxtitle->value." ".$oArticle->oxarticles__oxvarselect->value);
            }


            $oThisCurr = $myConfig->getCurrencyObject( $oPricealarm->oxpricealarm__oxcurrency->value);

            if ( !$oThisCurr ) {
                $oThisCurr = $oDefCurr;
                $oPricealarm->oxpricealarm__oxcurrency->setValue($oDefCurr->name);
            }

            // #889C - Netto prices in Admin
            // (we have to call $oArticle->getPrice() to get price with VAT)
            $oArticle->oxarticles__oxprice->setValue($oArticle->getPrice()->getBruttoPrice() * $oThisCurr->rate);
            $oArticle->fprice = oxLang::getInstance()->formatCurrency( $oArticle->oxarticles__oxprice->value, $oThisCurr);

            $oPricealarm->oxpricealarm__oxprice->setValue(oxLang::getInstance()->formatCurrency( $oPricealarm->oxpricealarm__oxprice->value, $oThisCurr));

            $oPricealarm->oArticle = $oArticle;
            $oCur = $myConfig->getCurrencyObject( $oPricealarm->oxpricealarm__oxcurrency->value);

            // customer info
            $oUser = null;
            if ( $oPricealarm->oxpricealarm__oxuserid->value) {
                $oUser = oxNew( "oxuser" );
                $oUser->load($oPricealarm->oxpricealarm__oxuserid->value);
                $oPricealarm->oUser = $oUser;
            }
            //
            $oShop = oxNew( "oxshop" );
            $oShop->load( $myConfig->getShopId());
            $oShop = $this->addGlobalParams( $oShop);

            $smarty = oxUtilsView::getInstance()->getSmarty();
            $smarty->assign( "shop", $oShop );
            $smarty->assign( "product", $oArticle );
            $smarty->assign( "bidprice", $oPricealarm->oxpricealarm__oxprice->value);
            $smarty->assign( "shopImageDir", $myConfig->getImageUrl( false , false ) );
            $smarty->assign( "currency", $oCur );

            $iLang = @$oPricealarm->oxpricealarm__oxlang->value;
            if (!$iLang)
                $iLang = 0;
            $aLanguages = oxLang::getInstance()->getLanguageNames();
            $this->_aViewData["edit_lang"] = $aLanguages[$iLang];
            // rendering mail message text
            $oLetter = new oxStdClass();
            $aParams = oxConfig::getParameter( "editval");
            if ( isset( $aParams['oxpricealarm__oxlongdesc'] ) && $aParams['oxpricealarm__oxlongdesc'] ) {
                $oLetter->oxpricealarm__oxlongdesc = new oxField( stripslashes( $aParams['oxpricealarm__oxlongdesc'] ), oxField::T_RAW );
            } else {
                $old_iLang = oxLang::getInstance()->getTplLanguage();
                oxLang::getInstance()->setTplLanguage( $iLang );
                $smarty->fetch( "email_pricealarm_customer.tpl");

                $oLetter->oxpricealarm__oxlongdesc = new oxField( $smarty->fetch( "email_pricealarm_customer.tpl"), oxField::T_RAW );
                oxLang::getInstance()->setTplLanguage( $old_iLang );
            }

            $this->_aViewData["editor"]  = $this->_generateTextEditor( "100%", 300, $oLetter, "oxpricealarm__oxlongdesc", "details.tpl.css");
            $this->_aViewData["edit"] =  $oPricealarm;
            $this->_aViewData["oxid"] = $soxId;
            $this->_aViewData["actshop"] = $oShop->getShopId();
        }

        parent::render();

        return "pricealarm_main.tpl";
    }

    /**
     * Sending email to selected customer
     *
     * @return null
     */
    public function send()
    {
        // error
        if ( !oxConfig::getParameter( "oxid")) {
            $this->_aViewData["mail_err"] = 1;
            return;
        }

        $oPricealarm = oxNew( "oxpricealarm" );
        $oPricealarm->load( oxConfig::getParameter( "oxid"));

        // Send Email
        $oShop = oxNew( "oxshop" );
        $oShop->load( $oPricealarm->oxpricealarm__oxshopid->value);
        $oShop = $this->addGlobalParams( $oShop);

        $oArticle = oxNew( "oxarticle" );
        $oArticle->load( $oPricealarm->oxpricealarm__oxartid->value);

        //arranging user email
        $oxEMail = oxNew( "oxemail" );
        $oxEMail->From     = $oShop->oxshops__oxorderemail->value;
        $oxEMail->FromName = $oShop->oxshops__oxname->value;
        $oxEMail->Host     = $oShop->oxshops__oxsmtp->value;
        $oxEMail->SetSMTP( $oShop);
        $oxEMail->WordWrap = 100;

        $aParams = oxConfig::getParameter( "editval" );
        $oxEMail->Body      = stripslashes( isset( $aParams['oxpricealarm__oxlongdesc'] ) ? $aParams['oxpricealarm__oxlongdesc'] : '' );
        $oxEMail->Subject   = $oShop->oxshops__oxname->value;
        $oxEMail->AddAddress( $oPricealarm->oxpricealarm__oxemail->value, $oPricealarm->oxpricealarm__oxemail->value );
        $oxEMail->AddReplyTo( $oShop->oxshops__oxorderemail->value, $oShop->oxshops__oxname->value);
        $blSuccess = $oxEMail->send();

        // setting result message
        if ( $blSuccess) {
            $timeout = time();
            $now = date("Y-m-d H:i:s", $timeout);
            $oPricealarm->oxpricealarm__oxsended->setValue($now);
            $oPricealarm->save();
            $this->_aViewData["mail_succ"] = 1;
        } else {
            $this->_aViewData["mail_err"] = 1;
        }
    }
}
