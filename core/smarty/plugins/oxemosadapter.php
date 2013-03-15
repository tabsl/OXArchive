<?php
/*
Copyright (c) 2004, 2005 ECONDA GmbH Karlsruhe
All rights reserved.

ECONDA GmbH
Haid-und-Neu-Str. 7
76131 Karlsruhe
Tel. +49 (721) 6635726
Fax +49 (721) 66499070
info@econda.de
www.econda.de


Redistribution and use in source and binary forms, with or without modification,
are permitted provided that the following conditions are met:

* Redistributions of source code must retain the above copyright notice,
this list of conditions and the following disclaimer.
* Redistributions in binary form must reproduce the above copyright notice,
this list of conditions and the following disclaimer in the documentation
and/or other materials provided with the distribution.
* Neither the name of the ECONDA GmbH nor the names of its contributors may
be used to endorse or promote products derived from this software without
specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/


/**
 * Includes emos script formatter class
 */
require_once oxConfig::getInstance()->getConfigParam( 'sCoreDir' ) . 'smarty/plugins/emos.php';

/**
 * This class is a reference implementation of a PHP Function to include
 * ECONDA Trackiong into a Shop-System.
 *
 * The smarty tempaltes should include s tag like
 * [{insert name="oxid_tracker" title=$template_title }]
 */
class oxEmosAdapter extends oxSuperCfg
{
    /**
     * Current view category path
     *
     * @var string
     */
    protected $_sEmosCatPath = null;

    /**
     * Emos object storage
     *
     * @var emos
     */
    protected $_oEmos = null;

    /**
     * oxEmosAdapter class instance.
     *
     * @var oxEmosAdapter instance
     */
    private static $_instance = null;

    /**
     * resturns a single instance of this class
     *
     * @return oxUtils
     */
    public static function getInstance()
    {
        if ( !self::$_instance instanceof oxEmosAdapter ) {
            self::$_instance = new oxEmosAdapter();
        }
        return self::$_instance;
    }

    /**
     * Returns path to econda script files
     *
     * @return string
     */
    protected function _getScriptPath()
    {
        $myConfig = $this->getConfig();
        $sShopUrl = $myConfig->isSsl() ? $myConfig->getConfigParam( 'sSSLShopURL' ) : $myConfig->getConfigParam( 'sShopURL' );
        return "{$sShopUrl}modules/econda/out/";
    }

    /**
     * Searches for product category id
     *
     * @param oxarticle $oArticle article object to search for category
     *
     * @return string
     */
    protected function _getDeepestCategoryPath( $oArticle )
    {
        $iLanguage = oxLang::getInstance()->getBaseLanguage();

        $sSuffix  = oxLang::getInstance()->getLanguageTag( $iLanguage );
        $sCatView = getViewName('oxcategories');
        $sO2CView = getViewName('oxobject2category');

        $sSelect  = "select {$sCatView}.oxtitle{$sSuffix} from $sO2CView as oxobject2category left join {$sCatView}
                     on {$sCatView}.oxid=oxobject2category.oxcatnid where
                     oxobject2category.oxobjectid = '".$oArticle->getId()."' and
                     {$sCatView}.oxid is not null order by oxobject2category.oxtime ";

        return oxDb::getDb()->getOne( $sSelect );
    }

    /**
     * Returns emos item object
     *
     * @return EMOS_Item
     */
    protected function _getNewEmosItem()
    {
        return new EMOS_Item();
    }

    /**
     * Returns new emos controller object
     *
     * @return
     */
    public function getEmos()
    {
        if ( $this->_oEmos === null ) {
            $this->_oEmos = new EMOS( $this->_getScriptPath() );

            // make output more readable
            $this->_oEmos->prettyPrint();
            $this->_oEmos->setSid( $this->getSession()->getId() );

            // set page id
            $this->_oEmos->addPageID( $this->_getEmosPageId( $this->_getTplName() ) );

            // language id
            $this->_oEmos->addLangID( oxLang::getInstance()->getBaseLanguage() );

            // set site ID
            $this->_oEmos->addSiteID( $this->getConfig()->getShopId() );
        }

        return $this->_oEmos;
    }

    /**
     * Converts a oxarticle object to an EMOS_Item
     *
     * @param oxarticle $oProduct article to convert
     * @param string    $sCatPath category path
     * @param int       $iQty     buyable amount
     *
     * @return EMOS_Item
     */
    protected function _convProd2EmosItem( $oProduct, $sCatPath = "NULL", $iQty = 1 )
    {
        $oItem = $this->_getNewEmosItem();
        $oItem->productID   = ( isset( $oProduct->oxarticles__oxartnum->value ) && $oProduct->oxarticles__oxartnum->value ) ? $oProduct->oxarticles__oxartnum->value : $oProduct->getId();
        $oItem->productName = $oProduct->oxarticles__oxtitle->value;

        // #810A
        $oCur = $this->getConfig()->getActShopCurrencyObject();
        $oItem->price        = $oProduct->getPrice()->getBruttoPrice() * ( 1/$oCur->rate );
        $oItem->productGroup = "{$sCatPath}/{$oProduct->oxarticles__oxtitle->value}";
        $oItem->quantity     = $iQty;

        return $oItem;
    }

    /**
     * Returns page title
     *
     * @param array $params parameters where product info is kept
     *
     * @return string
     */
    protected function _getEmosPageTitle( $aParams )
    {
        return isset( $aParams['title'] ) ? $aParams['title'] : null;
    }

    /**
     * Returns purpose of this page (current view name)
     *
     * @return string
     */
    protected function _getEmosCl()
    {
        $sCl = $this->getConfig()->getActiveView()->getClassName();
        return $sCl ? strtolower( $sCl ) : 'start';
    }

    /**
     * Returns current view category path
     *
     * @return string
     */
    function _getEmosCatPath()
    {
        if ( $this->_sEmosCatPath === null ) {
            $sCatPath = '';
            if ( ( $oActCatPath = $this->getConfig()->getActiveView()->getCatTreePath() ) ) {
                foreach( $oActCatPath as $oCat ) {
                    if ( $sCatPath ) {
                        $sCatPath .= '/';
                    }
                    $sCatPath .= $oCat->oxcategories__oxtitle->value;
                }
            }
            $this->_sEmosCatPath = ( $sCatPath ? $sCatPath : 'NULL' );
        }
        return $this->_sEmosCatPath;
    }

    /**
     * generates a unique id for the current page
     *
     * @param string $sTplName current view template name
     *
     * @return string
     */
    protected function _getEmosPageId( $sTplName )
    {
        $sPageId = $this->getConfig()->getShopId() .
                   $this->_getEmosCl() .
                   $sTplName .
                   oxConfig::getParameter( 'cnid' ) .
                   oxConfig::getParameter( 'anid' ) .
                   oxConfig::getParameter( 'option' );

        return md5( $sPageId );
    }

    /**
     * Returns active view template name
     *
     * @return string
     */
    protected function _getTplName()
    {
        if ( !( $sCurrTpl = basename( ( string ) oxConfig::getParameter( 'tpl' ) ) ) ) {
            // in case template was not defined in request
            $sCurrTpl = $this->getConfig()->getActiveView()->getTemplateName();
        }
        return $sCurrTpl;
    }

    /**
     * Builds JS code for current view tracking functionality
     *
     * @param array  $params  plugin parameters
     * @param smarty $oSmarty template engine object
     *
     * @return string
     */
    public function getCode( $aParams, $oSmarty )
    {
        $myConfig  = $this->getConfig();
        $mySession = $this->getSession();

        // current view object
        $oCurrView = $myConfig->getActiveView();

        // action name
        $sFnc = $oCurrView->getFncName();

        // current product (if available)
        $oProduct = ( isset( $aParams['product'] ) && $aParams['product'] ) ? $aParams['product'] : null;

        // session user
        $oUser = oxNew( 'oxuser' );
        if ( !$oUser->loadActiveUser() ) {
            $oUser = false;
        }

        // make a new emos instance for this call
        $oEmos = $this->getEmos();

        // current name of tmeplate
        $sTplName = $this->_getTplName();

        // current currency
        $oCur = $myConfig->getActShopCurrencyObject();

        // treat the different PageTypes
        switch ( $this->_getEmosCl() ) {
            case 'start':
                $oEmos->addContent( 'Start' );
                break;
            case 'basket':
                $oEmos->addContent( 'Shop/Kaufprozess/Warenkorb' );
                $oEmos->addOrderProcess( '1_Warenkorb' );
                break;
            case 'user':
                //ECONDA FIX track the OXID 3.x order process with the 3 different options plus default
                $sOption = oxConfig::getParameter( 'option' );
                $sOption = ( isset( $sOption ) ) ? $sOption : oxSession::getVar( 'option' );
                switch ( $sOption ) {
                    case '1':
                        $oEmos->addContent( 'Shop/Kaufprozess/Kundendaten/OhneReg' );
                        $oEmos->addOrderProcess( '2_Kundendaten/OhneReg' );
                        break;
                    case '2':
                        $oEmos->addContent( 'Shop/Kaufprozess/Kundendaten/BereitsKunde' );
                        $oEmos->addOrderProcess( '2_Kundendaten/BereitsKunde' );
                        break;
                    case '3':
                        $oEmos->addContent( 'Shop/Kaufprozess/Kundendaten/NeuesKonto' );
                        $oEmos->addOrderProcess( '2_Kundendaten/NeuesKonto' );
                        break;
                    default:
                        $oEmos->addContent( 'Shop/Kaufprozess/Kundendaten' );
                        $oEmos->addOrderProcess( '2_Kundendaten' );
                    break;
                }
                break;
            case 'payment':
                $oEmos->addContent( 'Shop/Kaufprozess/Zahlungsoptionen' );
                $oEmos->addOrderProcess( '3_Zahlungsoptionen' );
                break;
            case 'order':
                $oEmos->addContent( 'Shop/Kaufprozess/Bestelluebersicht' );
                $oEmos->addOrderProcess( '4_Bestelluebersicht' );
                break;
            case 'thankyou':
                $oEmos->addContent( 'Shop/Kaufprozess/Bestaetigung' );
                $oEmos->addOrderProcess( '5_Bestaetigung' );

                // get order Page Array
                //ECONDA FIX use username (email address) instead of customer number
                $oOrder  = $oCurrView->getOrder();
                $oBasket = $oCurrView->getBasket();
                $oEmos->addEmosBillingPageArray( $oOrder->oxorder__oxordernr->value,
                                                 $oUser->oxuser__oxusername->value,
                                                 $oBasket->getPrice()->getBruttoPrice() * ( 1 / $oCur->rate ),
                                                 $oOrder->oxorder__oxbillcountry->value,
                                                 $oOrder->oxorder__oxbillzip->value,
                                                 $oOrder->oxorder__oxbillcity->value );

                // get Basket Page Array
                $aBasket = array();
                $aBasketProducts = $oBasket->getContents();
                foreach ( $aBasketProducts as $oContent ) {
                    $oProduct = $oContent->getArticle();
                    $sPath = $this->_getDeepestCategoryPath( $oProduct );
                    $aBasket[] = $this->_convProd2EmosItem( $oProduct, $sPath, $oContent->getAmount() );
                }
                $oEmos->addEmosBasketPageArray( $aBasket );
                break;
            case 'details':
                if ( $oProduct ) {
                    $oEmos->addContent( 'Shop/'.$this->_getEmosCatPath().'/'.strip_tags( $oProduct->oxarticles__oxtitle->value ) );
                    $sPath = $this->_getDeepestCategoryPath( $oProduct );
                    $oEmos->addDetailView( $this->_convProd2EmosItem( $oProduct, $sPath, 1 ) );
                }
                break;
            case 'search':
                $oEmos->addContent( 'Shop/Suche' );
                $iPage = oxConfig::getParameter( 'pgNr' );
                if ( !$iPage ) { //ECONDA FIX only track first search page, not the following pages
                    // #1184M - specialchar search
                    $sSearchParamForLink = rawurlencode( oxConfig::getParameter( 'searchparam', true ) );
                    $sOutput .= $oEmos->addSearch( $sSearchParamForLink, $oSmarty->_tpl_vars['pageNavigation']->iArtCnt );
                }
                break;
            case 'alist':
                $oEmos->addContent( 'Shop/'.$this->_getEmosCatPath() );
                break;
            case 'account_wishlist':
                $oEmos->addContent( 'Service/Wunschzettel' );
                break;
            case 'contact':
                if ( !$oCurrView->getContactSendStatus() ){
                    $oEmos->addContent( 'Service/Kontakt/Form' );
                } else {
                    $oEmos->addContent( 'Service/Kontakt/Success' );
                    $oEmos->addContact( 'Kontakt-Formular' );
                }
                break;
            case 'help':
                $oEmos->addContent( 'Service/Hilfe' );
                break;
            case 'newsletter':
                if ( !$oCurrView->getNewsletterStatus() ) {
                    $oEmos->addContent( 'Service/Newsletter/Form' );
                } else {
                    $oEmos->addContent( 'Service/Newsletter/Success' );
                }
                break;
            case 'guestbook':
                $oEmos->addContent( 'Service/Gaestebuch' );
                break;
            case 'links':
                $oEmos->addContent( 'Service/Links' );
                break;
            case 'info':
                switch ( $sTplName ) {
                    case 'impressum.tpl':
                        $oEmos->addContent( 'Info/Impressum' );
                        break;
                    case 'agb.tpl':
                        $oEmos->addContent( 'Info/AGB' );
                        break;
                    case 'order_info.tpl':
                        $oEmos->addContent( 'Info/Bestellinfo' );
                        break;
                    case 'delivery_info.tpl':
                        $oEmos->addContent( 'Info/Versandinfo' );
                        break;
                    case 'security_info.tpl':
                        $oEmos->addContent( 'Info/Sicherheit' );
                        break;
                    default:
                        $oEmos->addContent( 'Content/'.preg_replace( '/\.tpl$/', '', $sTplName ) );
                        break;
                }
                break;
            case 'account':
                if ( $sFnc ) {
                    if ( $sFnc != 'logout' ) {
                        $oEmos->addContent( 'Login/Uebersicht' );
                    } else {
                        $oEmos->addContent( 'Login/Formular/Logout' );
                    }
                }else{
                    $oEmos->addContent( 'Login/Formular/Login' );
                }
                break;
            case 'account_user':
                $oEmos->addContent( 'Login/Kundendaten' );
                break;
            case 'account_order':
                $oEmos->addContent( 'Login/Bestellungen' );
                break;
            case 'account_noticelist':
                $oEmos->addContent( 'Login/Merkzettel' );
                break;
            case 'account_newsletter':
                $oEmos->addContent( 'Login/Newsletter' );
                break;
            case 'account_whishlist':
                $oEmos->addContent( 'Login/Wunschzettel' );
                break;
            case 'forgotpassword':
                $oEmos->addContent( 'Login/PW vergessen' );
                break;
            case 'content':
                $oEmos->addContent( 'Content/'.$this->_getEmosPageTitle( $aParams ) );
                break;
            case 'register':

                $oEmos->addContent( 'Service/Register' );

                $iError   = oxConfig::getParameter( 'newslettererror' );
                $iSuccess = oxConfig::getParameter( 'success' );

                if ( $iError && $iError < 0 ) {
                    $oEmos->addRegister( $oUser ? $oUser->getId() : 'NULL' , abs( $iError ) );
                }

                if ( $iSuccess && $iSuccess > 0 && $oUser ) {
                    $oEmos->addRegister( $oUser->getId() , 0 );
                }

                break;
            default:
                $oEmos->addContent( 'Content/'.preg_replace( '/\.tpl$/', '', $sTplName ) );
                break;
        }

        // get the last Call for special handling function "tobasket", "changebasket"
        if ( ( $aLastCall = oxSession::getVar( 'aLastcall' ) ) ) {
            oxSession::deleteVar( 'aLastcall' );
        }

        // ADD To Basket and Remove from Basket
        if ( is_array( $aLastCall ) && count( $aLastCall ) ) {
            $sCallAction = key( $aLastCall );
            $aCallData   = current( $aLastCall );

            switch ( $sCallAction ) {
                case 'changebasket':
                    foreach ( $aCallData as $sItemId => $aItemData ) {
                        $oProduct = oxNew( 'oxarticle' );
                        if ( $oProduct->load( $sItemId ) ) {
                            //ECONDA FIX always use the main category
                            $sPath = $this->_getDeepestCategoryPath( $oProduct );
                            $oEmos->removeFromBasket( $this->_convProd2EmosItem( $oProduct, $sPath, ( $aItemData['oldam'] - $aItemData['am'] ) ) );
                        }
                    }
                    break;
                case 'tobasket':
                    foreach ( $aCallData as $sItemId => $aItemData ) {
                        // ECONDA FIX if there is a "add to basket" in the artcle list view, we do not have a product ID here
                        $oProduct = oxNew( 'oxarticle' );
                        if ( $oProduct->load( $sItemId ) ) {
                            //ECONDA FIX always use the main category
                            $sPath = $this->_getDeepestCategoryPath( $oProduct );
                            $oEmos->addToBasket( $this->_convProd2EmosItem( $oProduct, $sPath , 1 ) );
                        }
                    }
                    break;
            }
        }

        // track logins
        if ( 'login_noredirect' == $sFnc ) {
            $oEmos->addLogin( oxConfig::getParameter( 'lgn_usr' ), $oUser ? '0' : '1' );
        }

        return "\n".$oEmos->toString();
    }
}