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
 * @package views
 * @copyright © OXID eSales AG 2003-2009
 * $Id: oxubase.php 14135 2008-11-11 13:54:45Z arvydas $
 */

/**
 * Includes extended class.
 */
require_once getShopBasePath() . 'views/oxview.php' ;

/**
 * Base view class.
 * Class is responsible for managing of components that must be
 * loaded and executed before any regular operation.
 */
class oxUBase extends oxView
{
    /**
     * Sign if any new component is added. On this case will be
     * executed components stored in oxBaseView::_aComponentNames
     * plus oxBaseView::_aComponentNames.
     * @var bool
     */
    protected $_blCommonAdded = false;

    /**
     * Names of components (classes) that are initiated and executed
     * before any other regular operation.
     * @var array
     */
    protected $_aComponentNames = array(
                                    'oxcmp_user'       => 1, // 0 means dont init if cached
                                    'oxcmp_lang'       => 1,
                                    'oxcmp_cur'        => 1,
                                    'oxcmp_shop'       => 1,
                                    'oxcmp_categories' => 0,
                                    'oxcmp_utils'      => 1,
                                    'oxcmp_news'       => 0,
                                    'oxcmp_basket'     => 1
                                  );

    /**
     * Names of components (classes) that are initiated and executed
     * before any other regular operation. User may modify this himself.
     * @var array
     */
    protected $_aUserComponentNames = array();

    /**
     * Current view product object
     *
     * @var oxarticle
     */
    protected $_oProduct = null;

    /**
     * Number of current list page.
     * @var integer
     */
    protected $_iActPage = null;

    /**
     * A list of articles.
     * @var array
     */
    protected $_aArticleList = null;

    /**
     * Vendor list object.
     * @var object
     */
    protected $_oVendorTree  = null;

    /**
     * Category tree object.
     * @var oxcategorylist
     */
    protected $_oCategoryTree  = null;

    /**
     * Top 5 article list.
     * @var array
     */
    protected $_aTop5ArticleList  = null;

    /**
     * Bargain article list.
     * @var array
     */
    protected $_aBargainArticleList  = null;

    /**
     * If order price to low
     * @var integer
     */
    protected $_iLowOrderPrice  = null;

    /**
     * Min order price
     * @var string
     */
    protected $_sMinOrderPrice  = null;

    /**
     * Real newsletter status
     * @var string
     */
    protected $_iNewsRealStatus  = null;

    /**
     * Executes parent method parent::init(). If oxUBase::_blCommonAdded
     * is true - in array oxUBase::_aComponentNames stores newly defined
     * component names and allready registered in oxUBase::_aUserComponentNames
     *
     * @return null
     */
    protected $_aBlockRedirectParams = array( 'fnc' );

    /**
     * Vendorlist for search
     * @var array
     */
    protected $_aVendorlist = null;

    /**
     * Root vendor object
     * @var object
     */
    protected $_oRootVendor = null;

    /**
     * Vendor id
     * @var string
     */
    protected $_sVendorId = null;

    /**
     * Category tree for search
     * @var array
     */
    protected $_aSearchCatTree = null;

    /**
     * Category more
     * @var object
     */
    protected $_oCatMore = null;

    /**
     * Has user news subscribed
     * @var bool
     */
    protected $_blNewsSubscribed = null;

    /**
     * Show shipping address
     * @var bool
     */
    protected $_blShowShipAddress = null;

    /**
     * Delivery address
     * @var object
     */
    protected $_oDelAddress = null;

    /**
     * Category tree path
     * @var string
     */
    protected $_sCatTreePath = null;

    /**
     * In non admin mode checks if request was NOT processed by seo handler.
     * If NOT, then tries to load alternative SEO url and if url is available -
     * redirects to it. If no alternative path was found - 404 header is emitted
     * and page is rendered
     *
     * @return null
     */
    protected function _processRequest()
    {
        $myUtils = oxUtils::getInstance();

        // non admin, request is not empty and was not processed by seo engine
        if ( $myUtils->seoIsActive() && !$this->isAdmin() && !isSearchEngineUrl() && ( $sStdUrl = getRequestUrl( '', true ) ) ) {

            // fetching standard url and looking for it in seo table
            if ( $this->_canRedirect() && ( $sRedirectUrl = oxNew('oxSeoDecoder')->fetchSeoUrl( $sStdUrl ) ) ) {
                $myUtils->redirect( $this->getConfig()->getShopURL() . $sRedirectUrl, false );
            } else {
                // emitting not found header + continuing output
                @header( "HTTP/1.0 404 Not Found" );

                $sShopId = $this->getConfig()->getShopId();
                $sLangId = oxLang::getInstance()->getBaseLanguage();
                $sIdent  = md5( strtolower( $sStdUrl ) . $sShopId . $sLangId );

                // logging "not found" url
                $oDb = oxDb::getDb();
                $oDb->execute( "replace oxseologs ( oxstdurl, oxident, oxshopid, oxlang )
                                values ( " . $oDb->quote( $sStdUrl ) . ", '{$sIdent}', '{$sShopId}', '{$sLangId}' ) " );
            }
        }

    }

    /**
     * Calls self::_processRequest(), initializes components which needs to
     * be loaded, sets current list type, calls parent::init()
     *
     * @return null
     */
    public function init()
    {
        $this->_processRequest();

        if ( oxConfig::getParameter( '_force_no_basket_cmp' ) ) {
            unset( $this->_aComponentNames['oxcmp_basket'] );
        }

        // as the objects are cached by dispatcher we have to watch out, that we don't add these components twice
        if ( !$this->_blCommonAdded ) {
            $this->_aComponentNames = array_merge( $this->_aComponentNames, $this->_aUserComponentNames );
            $this->_blCommonAdded = true;
        }

        // setting list type if needed
        $this->_setListType();

        parent::init();
    }

    /**
     * Sets active list type if it was not set by request
     *
     * @return null
     */
    protected function _setListType()
    {
        if ( !oxConfig::getParameter( 'listtype' ) && isset( $this->_sListType ) ) {
            oxConfig::getInstance()->setGlobalParameter( 'listtype', $this->_sListType );
        }
    }

    /**
     * Generates URL for page navigation
     *
     * @param string $sClass class name
     *
     * @return string $sUrl String with working page url.
     */
    public function generatePageNavigationUrl()
    {
//        $sClass = $this->_sThisAction;
        return $this->getConfig()->getShopHomeURL().$this->_getRequestParams( false );
    }

    /**
     * Adds page number parameter to url and returns modified url
     *
     * @param string $sUrl  url to add page number
     * @param string $iPage active page number
     * @param int    $iLang language id
     *
     * @return string
     */
    protected function _addPageNrParam( $sUrl, $iPage, $iLang = null )
    {
        if ( $iPage >= 0 ) {
            $sUrl .= ( ( strpos( $sUrl, '?' ) === false ) ? '?' : '&amp;' ) . 'pgNr='.$iPage;
        }
        return $sUrl;
    }

    /**
     * Generates variables for page navigation
     *
     * @return  stdClass    $pageNavigation Object with pagenavigation data
     */
    public function generatePageNavigation( )
    {

        startProfile('generatePageNavigation');
        // generate the page navigation
        $pageNavigation = new stdClass();
        $pageNavigation->NrOfPages = $this->_iCntPages;
        $pageNavigation->iArtCnt   = $this->_iAllArtCnt;
        $iActPage = $this->getActPage();
        $pageNavigation->actPage   = $iActPage + 1;

        $sUrl = $this->generatePageNavigationUrl( );

        if ( $iActPage > 0) {
            $pageNavigation->previousPage = $this->_addPageNrParam( $sUrl, $iActPage - 1 );
        }

        if ( $iActPage < $pageNavigation->NrOfPages - 1 ) {
            $pageNavigation->nextPage = $this->_addPageNrParam( $sUrl, $iActPage + 1 );
        }

        if ( $pageNavigation->NrOfPages > 1 ) {
            for ( $i=1; $i < $pageNavigation->NrOfPages + 1; $i++ ) {
                $page = new Oxstdclass();
                $page->url = $this->_addPageNrParam( $sUrl, $i - 1 );
                $page->selected = 0;
                if ( $i == $pageNavigation->actPage ) {
                    $page->selected = 1;
                }
                $pageNavigation->changePage[$i] = $page;
            }

            // first/last one
            $pageNavigation->firstpage = $this->_addPageNrParam( $sUrl, 0 );
            $pageNavigation->lastpage  = $this->_addPageNrParam( $sUrl, $pageNavigation->NrOfPages - 1 );
        }

        stopProfile('generatePageNavigation');

        return $pageNavigation;
    }

    /**
     * performs setup of aViewData according to iMinOrderPrice admin setting
     *
     * @return null
     */
    public function prepareMinimumOrderPrice4View()
    {
        $myConfig = $this->getConfig();
        $iMinOrderPrice = $myConfig->getConfigParam( 'iMinOrderPrice' );
        if ( isset( $iMinOrderPrice ) && $iMinOrderPrice) {
            $oBasket = $this->getSession()->getBasket();
            if ( !$oBasket || ( $oBasket && !$oBasket->getProductsCount() ) ) {
                return;
            }
            $oCur    = $myConfig->getActShopCurrencyObject();
            $dMinOrderPrice = $iMinOrderPrice * $oCur->rate;
            // Coupons and discounts should be considered in "Min order price" check
            if ( $dMinOrderPrice > ( $oBasket->getDiscountProductsPrice()->getBruttoSum() - $oBasket->getTotalDiscount()->getBruttoPrice() - $oBasket->getVoucherDiscount()->getBruttoPrice()) ) {
                $this->_iLowOrderPrice = 1;
                $this->_sMinOrderPrice = oxLang::getInstance()->formatCurrency( $dMinOrderPrice, $oCur );
            }
        }
    }

    /**
     * While ordering disables navigation controls if oxConfig::blDisableNavBars
     * is on and executes parent::render()
     *
     * @return null
     */
    public function render()
    {
        parent::render();

        if ( $this->getIsOrderStep() ) {

            // min. order price check
            $this->prepareMinimumOrderPrice4View();

            // disabling navigation during order ...
            if ( $this->getConfig()->getConfigParam( 'blDisableNavBars' ) ) {
                $this->_iNewsRealStatus = 1;
                $this->setShowNewsletter(0);
                // for old tpl. will be removed later
                $this->_aViewData['isnewsletter'] = 0;
                $this->setShowRightBasket(0);
                $this->setShowLeftBasket(0);
                $this->setShowTopBasket(0);
            }
        }
        // show baskets
        $this->_aViewData['bl_perfShowLeftBasket']  = $this->showLeftBasket();
        $this->_aViewData['bl_perfShowRightBasket'] = $this->showRightBasket();
        $this->_aViewData['bl_perfShowTopBasket']   = $this->showTopBasket();

        $this->_aViewData['loworderprice']      = $this->isLowOrderPrice();
        $this->_aViewData['minorderprice']      = $this->getMinOrderPrice();
        $this->_aViewData['isnewsletter_truth'] = $this->getNewsRealStatus();

        $this->_aViewData['noindex'] = $this->noIndex();
    }

    /**
     * Returns current view product object (if it is loaded)
     *
     * @return oxarticle
     */
    public function getViewProduct()
    {
        if ( $this->_oProduct ) {
            return $this->_oProduct;
        }
    }

    /**
     * Sets view product
     *
     * @param oxarticle $oProduct view product object
     *
     * @return null
     */
    public function setViewProduct( $oProduct )
    {
        $this->_oProduct = $oProduct;
    }

    /**
     * Returns view product list
     *
     * @return array
     */
    public function getViewProductList()
    {
        return $this->_aArticleList;
    }

    /**
     * Active page getter
     *
     * @return int
     */
    public function getActPage()
    {
        if ( $this->_iActPage === null ) {
            $this->_iActPage = ( int ) oxConfig::getParameter( 'pgNr' );
            $this->_iActPage = ( $this->_iActPage < 0 ) ? 0 : $this->_iActPage;
        }
        return $this->_iActPage;
    }

    /**
     * Returns active category set by categories component; if category is
     * not set by component - will create category object and will try to
     * load by id passed by request
     *
     * @return oxcategory
     */
    public function getActCategory()
    {
        // if active category is not set yet - trying to load it from request params
        // this may be usefull when category component was unable to load active category
        // and we still need some object to mount navigation info
        if ( $this->_oClickCat === null ) {

            $this->_oClickCat = false;
            $oCategory = oxNew( 'oxcategory' );
            if ( $oCategory->load( $this->getCategoryId() ) ) {
                $this->_oClickCat = $oCategory;
            }
        }

        return $this->_oClickCat;
    }

    /**
     * Active category setter
     *
     * @param oxcategory $oCategory active category
     *
     * @return null
     */
    public function setActCategory( $oCategory )
    {
        $this->_oClickCat = $oCategory;
    }

    /**
     * Active tag info object getter. Object properties:
     *  - sTag current tag
     *  - link link leading to tag article list
     *
     * @return oxstdclass
     */
    public function getActTag()
    {
        if ( $this->_oActTag === null ) {
            $this->_oActTag = new Oxstdclass();
            $this->_oActTag->sTag = oxConfig::getParameter("searchtag", 1);

            $sUrl = $this->getConfig()->getShopHomeURL();
            $this->_oActTag->link = "{$sUrl}cl=tag";
        }
        return $this->_oActTag;
    }

    /**
     * Returns active vendor set by categories component; if vendor is
     * not set by component - will create vendor object and will try to
     * load by id passed by request
     *
     * @return oxvendor
     */
    public function getActVendor()
    {
        // if active vendor is not set yet - trying to load it from request params
        // this may be usefull when category component was unable to load active vendor
        // and we still need some object to mount navigation info
        if ( $this->_oActVendor === null ) {

            $this->_oActVendor = false;
            $sVendorId = oxConfig::getParameter( 'cnid' );
            $sVendorId = $sVendorId ? str_replace( 'v_', '', $sVendorId ) : $sVendorId;

            $oVendor = oxNew( 'oxvendor' );

            if ( 'root' == $sVendorId ) {
                $oVendor->setId( $sVendorId );
                $oVendor->oxvendor__oxtitle     = new oxField( oxLang::getInstance()->translateString( 'byBrand' ) );
                $oVendor->oxcategories__oxtitle = clone( $oVendor->oxvendor__oxtitle );
                $this->_oActVendor = $oVendor;
            } elseif ( $oVendor->load( $sVendorId ) ) {
                $this->_oActVendor = $oVendor;
            }
        }

        return $this->_oActVendor;
    }

    /**
     * Active vendor setter
     *
     * @param oxvendor $oVendor active vendor
     *
     * @return null
     */
    public function setActVendor( $oVendor )
    {
        $this->_oActVendor = $oVendor;
    }

    /**
     * Returns fake object which is used to mount navigation info
     *
     * @return oxstdclass
     */
    public function getActSearch()
    {
        if ( $this->_oActSearch === null ) {
            $this->_oActSearch = new oxStdClass();
            $sUrl = $this->getConfig()->getShopHomeURL();
            $this->_oActSearch->link = "{$sUrl}cl=search";
        }
        return $this->_oActSearch;
    }

    /**
     * Returns active recommlist object which is used to mount navigation info
     *
     * @return object
     */
    public function getActRecommList()
    {
        if ( $this->_oActRecomm === null ) {
            $this->_oActVendor = false;
            $sRecommId = oxConfig::getParameter( 'recommid' );

            $oRecommList = oxNew( 'oxrecommlist' );

            if ( $oRecommList->load( $sRecommId ) ) {
                $this->_oActRecomm = $oRecommList;
            }
        }
        return $this->_oActRecomm;
    }

    /**
     * Returns category tree (if it is loaded)
     *
     * @return oxcategorylist
     */
    public function getCategoryTree()
    {
        return $this->_oCategoryTree;
    }

    /**
     * Category list setter
     *
     * @param oxcategorylist $oCatTree category tree
     *
     * @return null
     */
    public function setCategoryTree( $oCatTree )
    {
        $this->_oCategoryTree = $oCatTree;
    }

    /**
     * Returns vendor tree (if it is loaded0
     *
     * @return oxvendorlist
     */
    public function getVendorTree()
    {
        return $this->_oVendorTree;
    }

    /**
     * Vendor tree setter
     *
     * @param oxvendorlist $oVendorTree vendor tree
     *
     * @return null
     */
    public function setVendorTree( $oVendorTree )
    {
        $this->_oVendorTree = $oVendorTree;
    }

    /**
     * Loads article actions: top articles, bargain - right side and top 5 articles
     *
     * Template variables:
     *
     * <b>articlebargainlist</b>, <b>aTop5Articles</b>
     *
     * @return null
     */
    protected function _loadActions()
    {
        $this->_aViewData['articlebargainlist'] = $this->getBargainArticleList();
        $this->_aViewData['aTop5Articles']      = $this->getTop5ArticleList();
    }

    /**
     * Active category id tracker used when SEO is on to track active category and
     * keep correct navigation
     *
     * @param string $sCategoryId active category Id
     *
     * @return null
     */
    public function setSessionCategoryId( $sCategoryId )
    {
        oxSession::setVar( 'cnid', $sCategoryId );
    }

    /**
     * Active category id getter
     *
     * @return string
     */
    public function getSessionCategoryId()
    {
        return oxSession::getVar( 'cnid' );
    }

    /**
     * Iterates through list articles and performs list view specific tasks
     *
     * @return null
     */
    protected function _processListArticles()
    {
        $sAddParams = $this->getAddUrlParams();
        if ( $sAddParams && $this->_aArticleList ) {
            foreach ( $this->_aArticleList as $oArticle ) {
                $oArticle->appendLink( $sAddParams );
            }
        }
    }

    /**
     * Returns additional URL paramerets which must be added to list products urls
     *
     * @return string
     */
    public function getAddUrlParams()
    {
    }

    /**
     * get link of current view
     *
     * @param int $iLang requested language
     *
     * @return string
     */
    public function getLink($iLang = null)
    {
        $sLink = parent::getLink($iLang);
        if ($iPg = $this->getActPage()) {
            $sLink = $this->_addPageNrParam( $sLink, $iPg, $iLang );
        }
        return $sLink;
    }

    /**
     * Template variable getter. Returns Top 5 article list
     *
     * @return array
     */
    public function getTop5ArticleList()
    {
        if ( $this->_aTop5ArticleList === null ) {
            $this->_aTop5ArticleList = false;
            $myConfig = $this->getConfig();
            if ( $myConfig->getConfigParam( 'bl_perfLoadAktion' ) ) {
                // top 5 articles
                $oArtList = oxNew( 'oxarticlelist' );
                $oArtList->loadTop5Articles();
                if ( $oArtList->count() ) {
                    $this->_aTop5ArticleList = $oArtList;
                }
            }
        }
        return $this->_aTop5ArticleList;
    }

    /**
     * Template variable getter. Returns bargain article list
     *
     * @return array
     */
    public function getBargainArticleList()
    {
        if ( $this->_aBargainArticleList === null ) {
            $this->_aBargainArticleList = array();
            if ( $this->getConfig()->getConfigParam( 'bl_perfLoadAktion' ) ) {
                $oArtList = oxNew( 'oxarticlelist' );
                $oArtList->loadAktionArticles( 'OXBARGAIN' );
                if ( $oArtList->count() ) {
                    $this->_aBargainArticleList = $oArtList;
                }
            }
        }
        return $this->_aBargainArticleList;
    }

    /**
     * Template variable getter. Returns if order price is to low
     *
     * @return integer
     */
    public function isLowOrderPrice()
    {
        return $this->_iLowOrderPrice;
    }

    /**
     * Template variable getter. Returns min order price
     *
     * @return string
     */
    public function getMinOrderPrice()
    {
        return $this->_sMinOrderPrice;
    }

    /**
     * Template variable getter. Returns if newsletter is realy active (for user.tpl)
     *
     * @return integer
     */
    public function getNewsRealStatus()
    {
        return $this->_iNewsRealStatus;
    }

    /**
     * Checks if current request parameters does not block SEO redirection process
     *
     * @return bool
     */
    protected function _canRedirect()
    {
        foreach ( $this->_aBlockRedirectParams as $sParam ) {
            if ( oxConfig::getParameter( $sParam ) !== null ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Empty active product getter
     *
     * @return null
     */
    public function getProduct()
    {
    }

    /**
     * Template variable getter. Returns vendorlist for search
     *
     * @return array
     */
    public function getVendorlist()
    {
        return $this->_aVendorlist;
    }

    /**
     * Sets vendorlist for search
     *
     * @param array $aList
     *
     * @return null
     */
    public function setVendorlist( $aList )
    {
        $this->_aVendorlist = $aList;
    }

    /**
     * Sets root vendor
     *
     * @param object $oVendor
     *
     * @return null
     */
    public function setRootVendor( $oVendor )
    {
        $this->_oRootVendor = $oVendor;
    }

    /**
     * Template variable getter. Returns root vendor
     *
     * @return object
     */
    public function getRootVendor()
    {
        return $this->_oRootVendor;
    }

    /**
     * Template variable getter. Returns vendor id
     *
     * @return string
     */
    public function getVendorId()
    {
        if ( $this->_sVendorId === null ) {
            if ( ( $oVendor = $this->getActVendor() ) ) {
                $this->_sVendorId = $oVendor->getId();
            }
        }
        return $this->_sVendorId;
    }

    /**
     * Template variable getter. Returns category tree for search
     *
     * @return array
     */
    public function getSearchCatTree()
    {
        return $this->_aSearchCatTree;
    }

    /**
     * Sets category tree for search
     *
     * @param array $aTree
     *
     * @return null
     */
    public function setSearchCatTree( $aTree )
    {
        $this->_aSearchCatTree = $aTree;
    }

    /**
     * Template variable getter. Returns more category
     *
     * @return object
     */
    public function getCatMore()
    {
        return $this->_oCatMore;
    }

    /**
     * Sets more category
     *
     * @param object $oCat
     *
     * @return null
     */
    public function setCatMore( $oCat )
    {
        $this->_oCatMore = $oCat;
    }

    /**
     * Template variable getter. Returns if user subscribed for newsletter
     *
     * @return bool
     */
    public function isNewsSubscribed()
    {
        return $this->_blNewsSubscribed;
    }

    /**
     * Sets if user subscribed for newsletter
     *
     * @param bool $blNewsSubscribed
     *
     * @return null
     */
    public function setNewsSubscribed( $blNewsSubscribed )
    {
        $this->_blNewsSubscribed = $blNewsSubscribed;
    }

    /**
     * Template variable getter. Returns if show user shipping address
     *
     * @return bool
     */
    public function showShipAddress()
    {
        return $this->_blShowShipAddress;
    }

    /**
     * Sets if show user shipping address
     *
     * @param bool $blShowShipAddress
     *
     * @return null
     */
    public function setShowShipAddress( $blShowShipAddress )
    {
        $this->_blShowShipAddress = $blShowShipAddress;
    }

    /**
     * Template variable getter. Returns shipping address
     *
     * @return bool
     */
    public function getDelAddress()
    {
        return $this->_oDelAddress;
    }

    /**
     * Sets shipping address
     *
     * @param bool $oDelAddress
     *
     * @return null
     */
    public function setDelAddress( $oDelAddress )
    {
        $this->_oDelAddress = $oDelAddress;
    }

    /**
     * Template variable getter. Returns category path
     *
     * @return string
     */
    public function getCatTreePath()
    {
        return $this->_sCatTreePath;
    }
}
