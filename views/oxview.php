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
 * @copyright © OXID eSales AG 2003-2008
 * $Id: oxview.php 14088 2008-11-10 16:51:19Z tomas $
 */

/**
 * Base view class. Collects and passes data to template engine, sets some global
 * configuration parameters.
 */
class oxView extends oxSuperCfg
{
    /**
     * Array of component objects.
     *
     * @var object
     */
    protected $_oaComponents = array();

    /**
     * Array of data that is passed to template engine - array( "varName" => "varValue").
     *
     * @var array
     */
    protected $_aViewData = array();

    /**
     * Location of a executed class file.
     *
     * @var string
     */
    protected $_sClassLocation = null;

    /**
     * Name of running class method.
     *
     * @var string
     */
    protected $_sThisAction = null;

    /**
     * If this is a component we will have our parent view here.
     *
     * @var oxview
     */
    protected $_oParent = null;

    /**
     * Flag if current view is an order view
     *
     * @var bool
     */
    protected $_blIsOrderStep = false;

    /**
     * Flag if this objcet is a component or not
     *
     * @var bool
     */
    protected $_blIsComponent = false;

    /**
     * Active articles category object.
     *
     * @var oxcategory
     */
    protected $_oActCategory = null;

    /**
     * Active vendor object.
     *
     * @var oxvendor
     */
    protected $_oActVendor = null;

    /**
     * Active search object - Oxstdclass object which keeps navigation info
     *
     * @var oxstdclass
     */
    protected $_oActSearch = null;

    /**
     * Name of template file to render.
     *
     * @var string
     */
    protected $_sThisTemplate = null;

    /**
     * Array of component names.
     *
     * @var array
     */
    protected $_aComponentNames = array();

    /**
     * Cache sign to enable/disable use of cache.
     *
     * @var bool
     */
    protected $_blIsCallForCache = false;

    /**
     * ID of current view - generated php file.
     *
     * @var string
     */
    protected $_sViewId = null;

    /**
     * List type
     *
     * @var string
     */
    protected $_sListType = null;

    /**
     * Category ID
     *
     * @var string
     */
    protected $_sCategoryId = null;

    /**
     * Current view class name
     *
     * @var string
     */
    protected $_sClass = null;

    /**
     * Action function name
     *
     * @var string
     */
    protected $_sFnc = null;

    /**
     * Marker if user defined function was executed
     *
     * @var bool
     */
    protected static $_blExecuted = false;

    /**
     * Active category object.
     * @var object
     */
    protected $_oClickCat = null;

    /**
     * Additional params for url.
     * @var string
     */
    protected $_sAdditionalParams = null;

    /**
     * Marked which defines if current view is sortable or not
     * @var bool
     */
    protected $_blShowSorting = false;

    /**
     * Show right basket
     * @var bool
     */
    protected $_blShowRightBasket = null;

    /**
     * Show left basket
     * @var bool
     */
    protected $_blShowLeftBasket = null;

    /**
     * Show top basket
     * @var bool
     */
    protected $_blShowTopBasket = null;

    /**
     * Load currency option
     * @var bool
     */
    protected $_blLoadCurrency = null;

    /**
     * Load vendors option
     * @var bool
     */
    protected $_blLoadVendorTree = null;

    /**
     * Dont show emty cats
     * @var bool
     */
    protected $_blDontShowEmptyCats = null;

    /**
     * Trunsted shop id
     * @var string
     */
    protected $_sTrustedShopId = null;

    /**
     * Load language option
     * @var bool
     */
    protected $_blLoadLanguage = null;

    /**
     * Show category top navigation option
     * @var bool
     */
    protected $_blShowTopCatNav = null;

    /**
     * Item count in category top navigation
     * @var integer
     */
    protected $_iTopCatNavItmCnt = null;

    /**
     * Active charset
     * @var string
     */
    protected $_sCharSet = null;

    /**
     * Shop version
     * @var string
     */
    protected $_sVersion = null;

    /**
     * If current shop has demo version
     * @var bool
     */
    protected $_blDemoVersion = null;

    /**
     * Rss links
     * @var array
     */
    protected $_aRssLinks = null;

    /**
     * Display sorting in templates
     * @var bool
     */
    protected $_blActiveSorting = null;

    /**
     * List's "order by"
     * @var string
     */
    protected $_sListOrderBy = null;

    /**
     * Order directio of list
     * @var string
     */
    protected $_sListOrderDir = null;

    /**
     * Meta description
     * @var string
     */
    protected $_sMetaDescription = null;

    /**
     * Meta keywords
     * @var string
     */
    protected $_sMetaKeywords = null;

    /**
     * Active currency object.
     * @var object
     */
    protected $_oActCurrency = null;

    /**
     * Number of products in comparelist.
     * @var integer
     */
    protected $_iCompItemsCnt = null;

    /**
     * Searched wishlist user name.
     * @var string
     */
    protected $_sWishlistName = null;

    /**
     * Menue list
     * @var array
     */
    protected $_aMenueList = null;

    /**
     * Current view search engine indexing state:
     *     0 - index without limitations
     *     1 - no index / no follow
     *     2 - no index / follow
     */
    protected $_iViewIndexState = 0;

    /**
     * Display if newsletter must be displayed
     * @var bool
     */
    protected $_iNewsStatus = null;

    /**
     * Shop logo
     * @var string
     */
    protected $_sShopLogo = null;

    /**
     * Initiates all components stored, executes oxview::addGlobalParams.
     *
     * @return null
     */
    public function init()
    {
        // setting current view class name
        $this->_sThisAction = strtolower( get_class( $this ) );

        if ( !$this->_blIsComponent ) {
            // storing current view

            $blInit = true;


            // init all components if there are any
            foreach ( $this->_aComponentNames as $sComponentName => $blNotCacheable ) {
                // do not override initiated components
                if ( !isset( $this->_oaComponents[$sComponentName] ) ) {
                    // component objects MUST be created to support user called functions
                    $oComponent = oxNew( $sComponentName );
                    $oComponent->setParent( $this );
                    $oComponent->setThisAction( $sComponentName );
                    $this->_oaComponents[$sComponentName] = $oComponent;
                }

                // do we really need to initiate them ?
                if ( $blInit ) {
                    $this->_oaComponents[$sComponentName]->init();

                    // executing only is view does not have action method
                    if ( !method_exists( $this, $this->getFncName() ) ) {
                        $this->_oaComponents[$sComponentName]->executeFunction( $this->getFncName() );
                    }
                }
            }

            // assume that cached components does not affect this method ...
            $this->addGlobalParams();

            // enable sorting ?
            if ( $this->showSorting() ) {
                $this->prepareSortColumns();
            }
            $this->_aViewData['showsorting']    = $this->isSortingActive();
            $this->_aViewData["allsortcolumns"] = $this->getSortColumns();
            $this->_aViewData['listorderby']    = $this->getListOrderBy();
            $this->_aViewData['listorder']      = $this->getListOrderDirection();
        }
    }

    /**
     * Template variable getter. Returns true if sorting is on
     *
     * @return bool
     */
    public function showSorting()
    {
        return $this->_blShowSorting && $this->getConfig()->getConfigParam( 'blShowSorting' );
    }

    /**
     * If current view ID is not set - forms and returns view ID
     * according to language and currency.
     *
     * @return string $this->_sViewId
     */
    public function getViewId()
    {
        if ( $this->_sViewId ) {
            return $this->_sViewId;
        }

        //$iLang = (int) oxConfig::getParameter( 'language' );
        $iLang = oxLang::getInstance()->getBaseLanguage();

        $iCur  = (int) oxConfig::getParameter( 'currency' );


            $this->_sViewId =  "ox|$iLang|$iCur";

        return $this->_sViewId;
    }


    /**
     * If current reset ID is not set - forms and returns view ID
     * according to category and usergroup ....
     *
     * @return string
     */
    public function getViewResetId()
    {
        if ( $this->_sViewResetID ) {
            return $this->_sViewResetID;
        }

        $sCategoryID = $this->_oClickCat?$this->_oClickCat->getId():'-';
        $this->_sViewResetID =  "ox|cid={$sCategoryID}|cl=".$this->getClassName();
        return $this->_sViewResetID;
    }


    /**
     * If this object is not a component (oxview::_blIsComponent = false)
     * goes through oxview::_oaComponents list, executes oxview::list render
     * method and passes it's data to template engine. Returns an array of
     * parameters used by template engine.
     *
     * @return string current view template file name
     */
    public function render()
    {
        //sending all view to smarty
        $this->_aViewData['oView'] = $this;
        $this->_aViewData['oViewConf'] = $this->getViewConfig();

        // @deprecated
        $this->_aViewData['shop'] = $this->_aViewData['oViewConf'];

        $this->_aViewData['meta_description'] = $this->getMetaDescription();
        $this->_aViewData['meta_keywords'] = $this->getMetaKeywords();

        // only if this is no component
        if ( !$this->_blIsComponent ) {
            foreach ( array_keys( $this->_oaComponents ) as $sComponentName ) {
                $this->_aViewData[$sComponentName] = $this->_oaComponents[$sComponentName]->render();
            }

            // set url and form navigation string
            $this->_setNavigationParams();
        }

        return $this->_sThisTemplate;
    }

    /**
     * Sets and caches default parameters for shop object and returns it.
     *
     * Template variables:
     * <b>isdemoversion</b>, <b>shop</b>, <b>isdemoversion</b>,
     * <b>version</b>,
     * <b>iShopID_TrustedShops</b>,
     * <b>urlsign</b>
     *
     * @param oxShop $oShop current shop object
     *
     * @return object $oShop current shop object
     */
    public function addGlobalParams( $oShop = null)
    {
        $mySession  = $this->getSession();
        $myConfig   = $this->getConfig();

        //deprecated template vars

        $this->_aViewData['isnewsletter'] = true;
        $this->_aViewData['isvarianten'] = true;
        $this->_aViewData['isreview'] = true;
        $this->_aViewData['isaddsales'] = true;
        $this->_aViewData['isvoucher'] = true;
        $this->_aViewData['isdtaus'] = true;
        $this->_aViewData['ispricealarm'] = true;
        $this->_aViewData['iswishlist'] = true;
        $this->_aViewData['isipayment'] = true;
        $this->_aViewData['istrusted'] = true;
        $this->_aViewData['isfiltering'] = true;
        $this->_aViewData['isgooglestats'] = true;
        $this->_aViewData['iswishlist'] = true;
        $this->_aViewData['isstaffelpreis'] = true;

        // by default we allways display newsletter bar
        $this->_iNewsStatus = 1;

        $this->_aViewData['charset']       = $this->getCharSet();
        $this->_aViewData['version']       = $this->getShopVersion();
        $this->_aViewData['edition']       = $this->getShopEdition();
        $this->_aViewData['fulledition']   = $this->getShopFullEdition();
        $this->_aViewData['isdemoversion'] = $this->isDemoVersion();

        $this->_aViewData['isfiltering'] = true;

        // show baskets
        $this->_aViewData['bl_perfShowLeftBasket']  = $this->showLeftBasket();
        $this->_aViewData['bl_perfShowRightBasket'] = $this->showRightBasket();
        $this->_aViewData['bl_perfShowTopBasket']   = $this->showTopBasket();

        // allow currency swiching
        $this->_aViewData['bl_perfLoadCurrency'] = $this->loadCurrency();

        // show/hide vendors
        $this->_aViewData['bl_perfLoadVendorTree'] = $this->loadVendorTree();

        // show/hide empty categories
        $this->_aViewData['blDontShowEmptyCategories'] = $this->dontShowEmptyCategories();

        $this->_aViewData['iShopID_TrustedShops'] = $this->getTrustedShopId();

        // used for compatibility with older templates
        $this->_aViewData['fixedwidth'] = $myConfig->getConfigParam( 'blFixedWidthLayout' );
        $this->_aViewData['urlsign']    = '&';
        $this->_aViewData['wishid']    = oxConfig::getParameter( 'wishid' );
        $this->_aViewData['shownewbasketmessage'] = oxUtils::getInstance()->isSearchEngine()?0:$myConfig->getConfigParam( 'iNewBasketItemMessage' );

        $this->_aViewData['sListType'] = $this->getListType();

        // set additional params
        $this->_setAdditionalParams();
        $this->_aViewData["additionalparams"] = $this->getAdditionalParams();

        $this->_aViewData['bl_perfLoadLanguage'] = $this->isLanguageLoaded();

        // new navigation ?
        $this->_aViewData['showtopcatnavigation']   = $this->showTopCatNavigation();
        $this->_aViewData['topcatnavigationitmcnt'] = $this->getTopNavigationCatCnt();

        $this->_setNrOfArtPerPage();

        // assigning shop to view config ..
        $oViewConf = $this->getViewConfig();
        if ( $oShop ) {
            $oViewConf->setViewShop( $oShop, $this->_aViewData );
        }

        // @deprecated
        $this->_aViewData['shop'] = $this->getViewConfig();

        return $oViewConf;
    }

    /**
     * addRssFeed adds link to rss
     *
     * @param string $sTitle
     * @param string $sUrl
     * @access public
     * @return void
     */
    public function addRssFeed($sTitle, $sUrl, $key = null)
    {
        if (!is_array($this->_aRssLinks)) {
            $this->_aRssLinks = array();
        }
        if ($key === null) {
            $this->_aRssLinks[] = array('title'=>$sTitle, 'link' => $sUrl);
        } else {
            $this->_aRssLinks[$key] = array('title'=>$sTitle, 'link' => $sUrl);
        }

        $this->_aViewData['rsslinks'] = $this->getRssLinks();
    }

    /**
     * Sets value to parameter used by template engine.
     *
     * @param string $sPara  name of parameter to pass
     * @param string $sValue value of parameter
     *
     * @return null
     */
    public function addTplParam( $sPara, $sValue )
    {
        $this->_aViewData[$sPara] = $sValue;
    }

    /**
     * Retrieves from session or gets new sorting parameters for
     * search and category lists. Sets new sorting parameters
     * (reverse or new column sort) to session.
     *
     * Template variables:
     * <b>showsorting</b>, <b>listorderby</b>, <b>listorder</b>,
     * <b>allsortcolumns</b>
     *
     * Session variables:
     * <b>listorderby</b>, <b>listorder</b>
     *
     * @return null
     */
    public function prepareSortColumns()
    {
        $aSortColumns = $this->getConfig()->getConfigParam( 'aSortCols' );
        if ( count( $aSortColumns ) > 0 ) {

            $this->_blActiveSorting = true;
            $this->_aSortColumns = $aSortColumns;

            $sCnid = oxConfig::getParameter( 'cnid' );

            $sSortBy  = oxConfig::getParameter( 'listorderby' );
            $sSortDir = oxConfig::getParameter( 'listorder' );

            if ( !$sSortBy && $aSorting = $this->getSorting( $sCnid ) ) {
                $sSortBy  = $aSorting['sortby'];
                $sSortDir = $aSorting['sortdir'];
            }

            if ( $sSortBy && oxDb::getInstance()->isValidFieldName( $sSortBy ) &&
                 $sSortDir && oxUtils::getInstance()->isValidAlpha( $sSortDir ) ) {

                $this->_sListOrderBy  = $sSortBy;
                $this->_sListOrderDir = $sSortDir;

                // caching sorting config
                $this->setItemSorting( $sCnid, $sSortBy, $sSortDir );
            }
        }
    }

    /**
     * Sets additional parameters: cl, searchparam, searchcnid,
     * searchvendor, cnid.
     *
     * Template variables:
     * <b>additionalparams</b>
     *
     * @return null
     */
    protected function _setAdditionalParams()
    {
        // #1018A
        $this->_sAdditionalParams = 'cl='.$this->getConfig()->getActiveView()->getClassName();

        // #1834M - specialchar search
        $sSearchParamForLink = rawurlencode( oxConfig::getParameter( 'searchparam', true ) );
        if ( isset( $sSearchParamForLink ) ) {
            $this->_sAdditionalParams .= "&amp;searchparam={$sSearchParamForLink}";
        }

        if ( ( $sVar = oxConfig::getParameter( 'searchcnid' ) ) ) {
            $this->_sAdditionalParams .= '&amp;searchcnid='.rawurlencode( rawurldecode( $sVar ) );
        }
        if ( ( $sVar = oxConfig::getParameter( 'searchvendor' ) ) ) {
            $this->_sAdditionalParams .= '&amp;searchvendor='.rawurlencode( rawurldecode( $sVar ) );
        }
        if ( ( $sVar = oxConfig::getParameter( 'cnid' ) ) ) {
            $this->_sAdditionalParams .= '&amp;cnid='.rawurlencode( rawurldecode( $sVar ) );
        }
    }

    /**
     * Returns view config object
     *
     * @return oxviewconfig
     */
    public function getViewConfig()
    {
        if ( $this->_oViewConf === null ) {
            $this->_oViewConf = oxNew( 'oxViewConfig' );
        }

        return $this->_oViewConf;
    }

    /**
     * Sets number of articles per page to config value
     *
     * @return null
     */
    protected function _setNrOfArtPerPage()
    {
        $myConfig  = $this->getConfig();
        $aViewData = array();

        //setting default values to avoid possible errors showing article list
        $iNrofCatArticles = $myConfig->getConfigParam( 'iNrofCatArticles' );
        $iNrofCatArticles = ( $iNrofCatArticles) ? $iNrofCatArticles : 10;

        // checking if all needed data is set
        $aNrofCatArticles = $myConfig->getConfigParam( 'aNrofCatArticles' );
        if ( !is_array( $aNrofCatArticles ) || !isset( $aNrofCatArticles[0] ) ) {
            $myConfig->setConfigParam( 'aNrofCatArticles', array( $iNrofCatArticles ) );
        } else {
            $iNrofCatArticles = $aNrofCatArticles[0];
        }

        $oViewConf = $this->getViewConfig();
        //value from user input
        if ( ( $iUserArtPerPage = (int) oxConfig::getParameter( '_artperpage' ) ) ) {
            // performing floor() to skip such variants as 7.5 etc
            $iNrofArticles = ( $iUserArtPerPage > 100 ) ? 10 : abs( $iUserArtPerPage );
            // M45 Possibility to push any "Show articles per page" number parameter
            $iNrofCatArticles = ( in_array( $iNrofArticles, $aNrofCatArticles ) ) ? $iNrofArticles : $iNrofCatArticles;
            $oViewConf->setViewConfigParam( 'iartPerPage', $iNrofCatArticles );
            oxSession::setVar( '_artperpage', $iNrofCatArticles );
        } elseif ( ( $iSessArtPerPage = oxSession::getVar( '_artperpage' ) )&& is_numeric( $iSessArtPerPage ) ) {
            // M45 Possibility to push any "Show articles per page" number parameter
            $iNrofCatArticles = ( in_array( $iSessArtPerPage, $aNrofCatArticles ) ) ? $iSessArtPerPage : $iNrofCatArticles;
            $oViewConf->setViewConfigParam( 'iartPerPage', $iSessArtPerPage );
            $iNrofCatArticles = $iSessArtPerPage;
        } else {
            $oViewConf->setViewConfigParam( 'iartPerPage', $iNrofCatArticles );
        }

        //setting number of articles per page to config value
        $myConfig->setConfigParam( 'iNrofCatArticles', $iNrofCatArticles );

        $this->_aViewData = array_merge( $this->_aViewData, $aViewData );
    }

    /**
     * Override this function to return object it which is used to identify its seo meta info
     *
     * @return null
     */
    protected function _getSeoObjectId()
    {
    }

    /**
     * Sets the view parameter "meta_description"
     *
     * @param mixed $aInput    array of strings or string
     * @param int   $iLength   max length of result, -1 for no truncation
     * @param bool  $blDescTag if true - performs additional dublicate cleaning
     *
     * @return null
     */
    public function setMetaDescription ( $aInput, $iLength = 1024, $blDescTag = false )
    {
        if ( oxUtils::getInstance()->seoIsActive() && ( $sOxid = $this->_getSeoObjectId() ) ) {

            // found special meta description ?
            if ( ( $sDescription = oxSeoEncoder::getInstance()->getMetaData( $sOxid , 'oxdescription' ) ) ) {
                return $this->_sMetaDescription = $sDescription;
            }

        }

        return $this->_sMetaDescription = $this->_prepareMetaDescription( $aInput, $iLength, $blDescTag );
    }

    /**
     * Metatags - description and keywords - generator for search
     * engines. Uses string passed by parameters, cleans HTML tags,
     * string dublicates, special chars. Also removes strings defined
     * in $myConfig->aSkipTags (Admin area).
     *
     * @param mixed $aInput    array of strings or string
     * @param int   $iLength   max length of result, -1 for no truncation
     * @param bool  $blDescTag if true - performs additional dublicate cleaning
     *
     * @return  string  $sString    converted string
     */
    protected function _prepareMetaDescription( $aInput, $iLength = 1024, $blDescTag = false )
    {
        /* performance - we dont need a huge amount of initial text.
           assume that effective text may be double longer than $iLength
           and simple turncate it
        */

        $sString = $aInput;
        if ( is_array( $aInput ) ) {
            $sString = implode( '  ', $aInput );
        }

        if ( $iLength != -1 ) {
            $iELength = ( $iLength * 2 );
            $sString = substr( $sString, 0, $iELength );
        }

        // decoding html entities
        $sString = html_entity_decode( $sString );
        // stripping HTML tags
        $sString = strip_tags( $sString );

        // removing some special chars
        $aRemoveChars = array( "\"", "'", ".", ":", "!", "?", "\n", "\r", "\t", "\x95", "\xA0", ";" );
        $sString = str_replace( $aRemoveChars, ' ', $sString );

        // removing duplicat words
        if ( !$blDescTag ) {
            $sString = $this->_removeDuplicatedWords( $sString );
        }

        // some special cases
        $sString = str_replace( ' ,', ',', $sString );
        $aPattern = array( "/,[\s\+\-\*]*,/", "/\s+,/" );
        $sString = preg_replace( $aPattern, ',', $sString );;
        $sString = oxUtilsString::getInstance()->minimizeTruncateString( $sString, $iLength );

        return $sString;
    }

    /**
     * Sets the view parameter 'meta_keywords'
     *
     * @param mixed $aInput array of strings or string
     *
     * @return null
     */
    public function setMetaKeywords( $aInput )
    {
        if ( oxUtils::getInstance()->seoIsActive() && ( $sOxid = $this->_getSeoObjectId() )  ) {
            // found special meta description ?
            if ( ( $sKeywords = oxSeoEncoder::getInstance()->getMetaData( $sOxid, 'oxkeywords' ) ) ) {
                return $this->_sMetaKeywords = $sKeywords;
            }
        }

        return $this->_sMetaKeywords = $this->_prepareMetaKeyword( $aInput );
    }

    /**
     * creates a stirng of keyword filtered by the function prepareMetaDescription and without any duplicates
     * additional the admin defined strings are removed
     *
     * @param mixed $aInput array of strings or string
     *
     * @return string of keywords seperated by comma
     */
    protected function _prepareMetaKeyword( $aInput )
    {
        $sString = $this->_prepareMetaDescription( $aInput, -1, true );
        $sString = $this->_removeDuplicatedWords( $sString );
        // removing in admin defined strings
        $aSkipTags = $this->getConfig()->getConfigParam( 'aSkipTags' );
        if ( is_array( $aSkipTags ) && $sString ) {
            foreach ( $aSkipTags as $sSkip ) {
                $aPattern = array( '/\W'.$sSkip.'\W/i', '/^'.$sSkip.'\W/i', '/\"'.$sSkip.'$/i' );
                $sString  = preg_replace( $aPattern, '', $sString);
            }
        }
        return $sString;
    }

    /**
     * Removes duplicated words (not case sensitive)
     *
     * @param mixed $aInput array of string or string
     *
     * @return string of words seperated by comma
     */
    protected function _removeDuplicatedWords( $aInput )
    {
        if ( is_array( $aInput ) ) {
            $aStrings = $aInput;
        } else {
            //is String
            $aStrings = preg_split( "/[\s,]+/", $aInput );
        }

        foreach ( $aStrings as $iANum => $sAString ) {
            foreach ( $aStrings as $iBNum => $sBString ) {
                // duplicates
                if ( $sAString && $iANum != $iBNum && !strcasecmp( $sAString, $sBString ) ) {
                    unset( $aStrings[$iANum] );
                }
            }
        }

        return implode( ', ', $aStrings );
    }

    /**
     * Formats url/form navigation parameters
     *
     * @return array navigation strings for url and form
     */
    protected function _setNavigationParams()
    {
        $aParams = $this->_getNavigationParams();

        $aNavString['url']  = '';
        $aNavString['form'] = '';

        foreach ( $aParams as $sKey => $sValue ) {
            if ( $sValue ) {

                $aNavString['url'] .= '&amp;'.$sKey.'='.rawurlencode( $sValue );

                // get searchparam for form in different way
                if ( $sKey == 'searchparam' )
                    $sValue = oxConfig::getParameter( 'searchparam' );

                $aNavString['form'] .= "<input type=\"hidden\" name=\"$sKey\" value=\"$sValue\">\n";
            }
        }

        $oViewConf = $this->getViewConfig();
        $oViewConf->setViewConfigParam( 'navurlparams', $aNavString['url'] );
        $oViewConf->setViewConfigParam( 'navformparams', $aNavString['form'] );
    }

    /**
     * Returns array of params => values which are used in hidden forms and as additional url params
     *
     * @return array
     */
    protected function _getNavigationParams()
    {
        $aParams['cnid']     = $this->getCategoryId();
        $aParams['listtype'] = $this->getListType();

        $aParams['recommid']     = oxConfig::getParameter( 'recommid' );
        $aParams['searchrecomm'] = oxConfig::getParameter( 'searchrecomm', true );
        $aParams['searchparam']  = oxConfig::getParameter( 'searchparam', true );
        $aParams['searchcnid']   = oxConfig::getParameter( 'searchcnid' );
        $aParams['searchvendor'] = oxConfig::getParameter( 'searchvendor' );
        $aParams['searchtag']    = oxConfig::getParameter( 'searchtag', 1 );

        return $aParams;
    }

    /**
     * Get list type
     *
     * @return string list type
     */
    public function getListType()
    {
        if ( $this->_sListType == null && ( $sListType = oxConfig::getParameter( 'listtype' ) ) ) {
            $this->_sListType = $sListType;
        }
        return $this->_sListType;
    }

    /**
     * List type setter
     *
     * @param string $sType type of list
     *
     * @return null
     */
    public function setListType( $sType )
    {
        $this->_sListType = $sType;
    }

    /**
     * Get category ID
     *
     * @return string $sListType
     */
    public function getCategoryId()
    {
        if ( $this->_sCategoryId == null && ( $sCatId = oxConfig::getParameter( 'cnid' ) ) ) {
            $this->_sCategoryId = $sCatId;
        }

        return $this->_sCategoryId;
    }

    /**
     * Category ID setter
     *
     * @param string $sCategoryId Id of category to cache
     *
     * @return null
     */
    public function setCategoryId( $sCategoryId )
    {
        $this->_sCategoryId = $sCategoryId;
    }

    /**
     * Returns current view template file name
     *
     * @return string
     */
    public function getTemplateName()
    {
        return $this->_sThisTemplate;
    }

    /**
     * Current view class name setter.
     *
     * @param string $sClassName current view class name
     *
     * @return null
     */
    public function setClassName( $sClassName )
    {
        $this->_sClass = $sClassName;
    }

    /**
     * Returns class name of current class
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->_sClass;
    }

    /**
     * Set current view action function name
     *
     * @param string $sFncName action function name
     *
     * @return null
     */
    public function setFncName( $sFncName )
    {
        $this->_sFnc = $sFncName;
    }

    /**
     * Returns name of current action function
     *
     * @return string
     */
    public function getFncName()
    {
        return $this->_sFnc;
    }

    /**
     * Set array of component objects
     *
     * @param array $aComponents array of components objects
     *
     * @return null
     */
    public function setComponents( $aComponents = null )
    {
        $this->_oaComponents = $aComponents;
    }

    /**
     * Get array of component objects
     *
     * @return array
     */
    public function getComponents()
    {
        return $this->_oaComponents;
    }

    /**
     * Set array of data that is passed to template engine - array( "varName" => "varValue")
     *
     * @param array $aViewData array of data that is passed to template engine
     *
     * @return null
     */
    public function setViewData( $aViewData = null )
    {
        $this->_aViewData = $aViewData;
    }

    /**
     * Get view data
     *
     * @return array
     */
    public function getViewData()
    {
        return $this->_aViewData;
    }

    /**
     * Get view data single array element
     *
     * @param string $sParamId view data array key
     *
     * @return mixed
     */
    public function getViewDataElement( $sParamId = null )
    {
        if ( $sParamId && isset( $this->_aViewData[$sParamId] ) ) {
            return $this->_aViewData[$sParamId];
        }
    }

    /**
     * Set location of a executed class file
     *
     * @param string $sClassLocation location of a executed class file
     *
     * @return null
     */
    public function setClassLocation( $sClassLocation = null )
    {
        $this->_sClassLocation = $sClassLocation;
    }
    /**
     * Get location of a executed class file
     *
     * @return string
     */
    public function getClassLocation()
    {
        return $this->_sClassLocation;
    }

    /**
     * Set name of running class method
     *
     * @param string $sThisAction name of running class method
     *
     * @return null
     */
    public function setThisAction( $sThisAction = null )
    {
        $this->_sThisAction = $sThisAction;
    }

    /**
     * Get name of running class method
     *
     * @return string
     */
    public function getThisAction()
    {
        return $this->_sThisAction;
    }

    /**
     * Set parent object. If this is a component we will have our parent view here.
     *
     * @param object $oParent parent object
     *
     * @return null
     */
    public function setParent( $oParent = null )
    {
        $this->_oParent = $oParent;
    }

    /**
     * Get parent object
     *
     * @return null
     */
    public function getParent()
    {
        return $this->_oParent;
    }

    /**
     * Set flag if current view is an order view
     *
     * @param bool $blIsOrderStep flag if current view is an order view
     *
     * @return null
     */
    public function setIsOrderStep( $blIsOrderStep = null )
    {
        $this->_blIsOrderStep = $blIsOrderStep;
    }

    /**
     * Get flag if current view is an order view
     *
     * @return bool
     */
    public function getIsOrderStep()
    {
        return $this->_blIsOrderStep;
    }

    /**
     * Set flag if this object is a component or not
     *
     * @param bool $blIsComponent flag if this object is a component
     *
     * @return null
     */
    public function setIsComponent( $blIsComponent = null )
    {
        $this->_blIsComponent = $blIsComponent;
    }

    /**
     * Get flag if this objcet is a component
     *
     * @return bool
     */
    public function getIsComponent()
    {
        return $this->_blIsComponent;
    }

    /**
     * Sets sorting item config
     *
     * @param string $sCnid    sortable item id
     * @param string $sSortBy  sort field
     * @param string $sSortDir sort direction (optional)
     *
     * @return null
     */
    public function setItemSorting( $sCnid, $sSortBy, $sSortDir = null )
    {
        $aSorting = oxSession::getVar( 'aSorting' );
        $aSorting[$sCnid]['sortby']  = $sSortBy;
        $aSorting[$sCnid]['sortdir'] = $sSortDir?$sSortDir:null;

        oxSession::setVar( 'aSorting', $aSorting );
    }

    /**
     * Returns sorting config for current item
     *
     * @param string $sCnid sortable item id
     *
     * @return string
     */
    public function getSorting( $sCnid )
    {
        $aSorting = oxSession::getVar( 'aSorting' );
        if ( isset( $aSorting[$sCnid] ) ) {
            return $aSorting[$sCnid];
        }
    }

    /**
     * Returns part of SQL query with sorting params
     *
     * @param string $sCnid sortable item id
     *
     * @return string
     */
    public function getSortingSql( $sCnid )
    {
        $aSorting = $this->getSorting( $sCnid );
        if ( is_array( $aSorting ) ) {
            return implode( " ", $aSorting );
        }
    }

    /**
     * Set cache sign to enable/disable use of cache
     *
     * @param bool $blIsCallForCache cache sign to enable/disable use of cache
     *
     * @return null
     */
    public function setIsCallForCache( $blIsCallForCache = null )
    {
        $this->_blIsCallForCache = $blIsCallForCache;
    }

    /**
     * Get cache sign to enable/disable use of cache
     *
     * @return bool
     */
    public function getIsCallForCache()
    {
        return $this->_blIsCallForCache;
    }

    /**
     * Executes method (creates class and then executes). Returns executed
     * function result.
     *
     * @param string $sFunction    name of function to execute
     * @throws oxSystemComponentException system component exception
     *
     * @return mixed
     */
    public function executeFunction( $sFunction )
    {
        $sNewAction = null;

        // execute
        if ( $sFunction && !self::$_blExecuted ) {
            if ( method_exists( $this, $sFunction ) ) {


                $sNewAction = $this->$sFunction();
                self::$_blExecuted = true;
            }

            // was not executed on any level ?
            if ( !$this->_blIsComponent && !self::$_blExecuted ) {
                $oEx = oxNew( 'oxSystemComponentException' );
                $oEx->setMessage( 'EXCEPTION_SYSTEMCOMPONENT_FUNCTIONNOTFOUND' );
                $oEx->setComponent( $sFunction );
                throw $oEx;
            }
        }

        $this->_executeNewAction( $sNewAction );
    }

    /**
     * Formats header for new controller action
     *
     * Input example: "[component_name@]view_name[/function_name]?param1=val1&param2=val2"
     * Parameters in [] are optional.
     *
     * @param string $sNewAction new action params
     *
     * @return string
     */
    protected function _executeNewAction( $sNewAction )
    {
        if ( $sNewAction ) {
            $myConfig  = $this->getConfig();

            // page parameters is the part which goes after '?'
            $aParams = explode( '?', $sNewAction );

            // action parameters is the part before '?'
            $sPageParams = isset( $aParams[1] )?$aParams[1]:null;

            // looking for function name
            $aParams    = explode( '/', $aParams[0] );
            $sClassName = $aParams[0];

            // looking for component name
            $aParams    = explode( '@', $aParams[0] );
            $sCmpName   = ( count( $aParams ) > 1 )?$aParams[0]:null;
            $sClassName = ( $sCmpName !== null )?$aParams[1]:$sClassName;

            // building redirect path ...
            $sHeader  = ( $sClassName )?"cl=$sClassName&":'';  // adding view name
            $sHeader .= ( $sPageParams )?"$sPageParams&":'';   // adding page params
            $sHeader .= $this->getSession()->sid();       // adding session Id

            // choosing URL to redirect
            $sURL = $myConfig->isSsl()?$myConfig->getSslShopUrl():$myConfig->getShopUrl();

            // different redirect URL in SEO mode
            if ( $this->isAdmin() ) {
                $sURL .= $myConfig->getConfigParam( 'sAdminDir' ) . '/';
            }

            $sURL = "{$sURL}index.php?{$sHeader}";

            //#M341 do not add redirect parameter
            oxUtils::getInstance()->redirect( $sURL, (bool) oxConfig::getParameter( 'redirected' ) );
        }
    }

    /**
     * Template variable getter. Returns additional params for url
     *
     * @return string
     */
    public function getAdditionalParams()
    {
        return $this->_sAdditionalParams;
    }

    /**
     * Marks that current view is marked as noindex, nofollow and
     * article details links must contain nofollow tags
     *
     * @return int
     */
    public function noIndex()
    {
        if ( oxConfig::getParameter( 'cur' ) ) {
            return $this->_iViewIndexState = 1;
        }

        switch ( oxConfig::getParameter( 'fnc' ) ) {
            case 'tocomparelist':
            case 'tobasket':
                return $this->_iViewIndexState = 1;
            default: break;
        }
        return $this->_iViewIndexState;
    }

    /**
     * Returns title suffix used in template
     *
     * @return string
     */
    public function getTitleSuffix()
    {
        return $this->getConfig()->getActiveShop()->oxshops__oxtitlesuffix->value;
    }

    /**
     * Returns title prefix used in template
     *
     * @return string
     *
     */
    public function getTitlePrefix()
    {
        return $this->getConfig()->getActiveShop()->oxshops__oxtitleprefix->value;
    }

    /**
     * returns object, assosiated with current view.
     * (the object that is shown in frontend)
     *
     * @return object
     */
    protected function getSubject()
    {
        return null;
    }

    /**
     * returns additional url params for dynamic url building
     *
     * @return string
     */
    public function getDynUrlParams()
    {
        $sRet = '';
        $sListType = $this->getListType();

        switch ($sListType) {
            default:
                break;
            case 'search':
                $sRet .= "&amp;listtype={$sListType}";
                if ( $sSearchParamForLink = rawurlencode( oxConfig::getParameter( 'searchparam', true ) ) ) {
                    $sRet .= "&amp;searchparam={$sSearchParamForLink}";
                }

                if ( ( $sVar = oxConfig::getParameter( 'searchcnid' ) ) ) {
                    $sRet .= '&amp;searchcnid='.rawurlencode( rawurldecode( $sVar ) );
                }
                if ( ( $sVar = oxConfig::getParameter( 'searchvendor' ) ) ) {
                    $sRet .= '&amp;searchvendor='.rawurlencode( rawurldecode( $sVar ) );
                }
               break;
        }

        return $sRet;
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
        if ( !isset( $iLang ) ) {
            $iLang = oxLang::getInstance()->getBaseLanguage();
        }

        $oDisplayObj = null;
        if ( oxUtils::getInstance()->seoIsActive() ) {
            $blTrySeo = true;
            $oDisplayObj = $this->getSubject();
        }

        if ( $oDisplayObj ) {
            return $oDisplayObj->getLink( $iLang );
        } else {
            $myConfig = oxConfig::getInstance();

            if ( $blTrySeo ) {
                $oEncoder = oxSeoEncoder::getInstance();
                if ( ( $sSeoUrl = $oEncoder->getStaticUrl( $myConfig->getShopHomeURL() . $this->_getSeoRequestParams(), $iLang ) ) ) {
                    return $sSeoUrl;
                }
            }

            $sForceLangChange = '';
            if ( oxLang::getInstance()->getBaseLanguage() != $iLang ) {
                $sForceLangChange = "&amp;lang={$iLang}";
            }

            // fallback to old non seo url
            return $myConfig->getShopCurrentURL( $iLang ) . $this->_getRequestParams() . $sForceLangChange;
        }
    }

    /**
     * Returns similar recommendation list
     * So far this method is implemented in Details (details.php) view.
     *
     * @return null
     */
    public function getSimilarRecommLists()
    {
        return null;
    }

    /**
     * collects _GET parameters used by eShop and returns uri
     *
     * @param bool $blAddPageNr
     *
     * @return string
     */
    protected function _getRequestParams( $blAddPageNr  = true )
    {

        $sClass = $this->getClassName();
        $sFnc   = $this->getFncName();

        // #921 S
        $aFnc = array( 'tobasket', 'login_noredirect', 'addVoucher' );
        if ( in_array( $sFnc, $aFnc ) ) {
            $sFnc = '';
        }

        // #680
        $sURL = "cl={$sClass}";
        if ( $sFnc ) {
            $sURL       .= "&amp;fnc={$sFnc}";
        }
        if ( $sVal = oxConfig::getParameter( 'cnid' ) ) {
            $sURL       .= "&amp;cnid={$sVal}";
        }
        if ( $sVal= oxConfig::getParameter( 'anid' ) ) {
            $sURL .= "&amp;anid={$sVal}";
        }

        if ( $sVal = basename( oxConfig::getParameter( 'page' ) ) ) {
            $sURL       .= "&amp;page={$sVal}";
        }

        if ( $sVal = basename( oxConfig::getParameter( 'tpl' ) ) ) {
            $sURL       .= "&amp;tpl={$sVal}";
        }

        $iPgNr = (int) oxConfig::getParameter( 'pgNr' );
        // don't include page number for navigation
        // it will be done in oxubase::generatePageNavigation
        if ( $blAddPageNr && $iPgNr > 0 ) {
            $sURL .= "&amp;pgNr={$iPgNr}";
        }

        // #1184M - specialchar search
        if ( $sVal = rawurlencode( oxConfig::getParameter( 'searchparam', true ) ) ) {
            $sURL .= "&amp;searchparam={$sVal}";
        }

        if ( $sVal = oxConfig::getParameter( 'searchcnid' ) ) {
            $sURL .= "&amp;searchcnid={$sVal}";
        }

        if ( $sVal = oxConfig::getParameter( 'searchvendor' ) ) {
            $sURL .= "&amp;searchvendor={$sVal}";
        }

        if ( $sVal = oxConfig::getParameter( 'searchrecomm' ) ) {
            $sUrl .= "&amp;searchrecomm={$sVal}";
        }

        if ( $sVal = oxConfig::getParameter( 'searchtag' ) ) {
            $sUrl .= "&amp;searchtag={$sVal}";
        }

        if ( $sVal = oxConfig::getParameter( 'recommid' ) ) {
            $sURL .= "&amp;recommid={$sVal}";
        }

        return $sURL;
    }

    /**
     * collects _GET parameters used by eShop SEO and returns uri
     *
     * @return string
     */
    protected function _getSeoRequestParams()
    {

        $sClass = $this->getClassName();
        $sFnc   = $this->getFncName();

        // #921 S
        $aFnc = array( 'tobasket', 'login_noredirect', 'addVoucher' );
        if ( in_array( $sFnc, $aFnc ) ) {
            $sFnc = '';
        }

        // #680
        $sURL = "cl={$sClass}";
        if ( $sFnc ) {
            $sURL       .= "&amp;fnc={$sFnc}";
        }
        if ( $sVal = basename( oxConfig::getParameter( 'page' ) ) ) {
            $sURL       .= "&amp;page={$sVal}";
        }

        if ( $sVal = basename( oxConfig::getParameter( 'tpl' ) ) ) {
            $sURL       .= "&amp;tpl={$sVal}";
        }

        $iPgNr = (int) oxConfig::getParameter( 'pgNr' );
        if ( $iPgNr > 0 ) {
            $sURL .= "&amp;pgNr={$iPgNr}";
        }

        return $sURL;
    }

    /**
     * Returns show right basket
     *
     * @return bool
     */
    public function showRightBasket()
    {
        if ( $this->_blShowRightBasket === null ) {
            if ( $blShowRightBasket = $this->getConfig()->getConfigParam( 'bl_perfShowRightBasket' ) )  {
                $this->_blShowRightBasket = $blShowRightBasket;
            }
        }
        return $this->_blShowRightBasket;
    }

    /**
     * Returns show right basket
     *
     * @param bool $blShowBasket
     *
     * @return null
     */
    public function setShowRightBasket( $blShowBasket )
    {
        $this->_blShowRightBasket = $blShowBasket;
    }

    /**
     * Returns show left basket
     *
     * @return bool
     */
    public function showLeftBasket()
    {
        if ( $this->_blShowLeftBasket === null ) {
            if ( $blShowLeftBasket = $this->getConfig()->getConfigParam( 'bl_perfShowLeftBasket' ) ) {
                $this->_blShowLeftBasket = $blShowLeftBasket;
            }
        }
        return $this->_blShowLeftBasket;
    }

    /**
     * Returns show left basket
     *
     * @param bool $blShowBasket
     *
     * @return null
     */
    public function setShowLeftBasket( $blShowBasket )
    {
        $this->_blShowLeftBasket = $blShowBasket;
    }

    /**
     * Returns show top basket
     *
     * @return bool
     */
    public function showTopBasket()
    {
        if ( $this->_blShowTopBasket === null ) {
            if ( $blShowTopBasket = $this->getConfig()->getConfigParam( 'bl_perfShowTopBasket' ) ) {
                $this->_blShowTopBasket = $blShowTopBasket;
            }
        }
        return $this->_blShowTopBasket;
    }

    /**
     * Returns show top basket
     *
     * @param bool $blShowBasket
     *
     * @return null
     */
    public function setShowTopBasket( $blShowBasket )
    {
        $this->_blShowTopBasket = $blShowBasket;
    }

    /**
     * Returns currency swiching option
     *
     * @return bool
     */
    public function loadCurrency()
    {
        if ( $this->_blLoadCurrency == null ) {
            $this->_blLoadCurrency = false;
            if ( $blLoadCurrency = $this->getConfig()->getConfigParam( 'bl_perfLoadCurrency' ) ) {
                $this->_blLoadCurrency = $blLoadCurrency;
            }
        }
        return $this->_blLoadCurrency;
    }

    /**
     * Returns if show/hide vendors
     *
     * @return bool
     */
    public function loadVendorTree()
    {
        if ( $this->_blLoadVendorTree == null ) {
            $this->_blLoadVendorTree = false;
            if ( $blLoadVendorTree = $this->getConfig()->getConfigParam( 'bl_perfLoadVendorTree' ) ) {
                $this->_blLoadVendorTree = $blLoadVendorTree;
            }
        }
        return $this->_blLoadVendorTree;
    }

    /**
     * Returns true if empty categories are not loaded
     *
     * @return bool
     */
    public function dontShowEmptyCategories()
    {
        if ( $this->_blDontShowEmptyCats == null ) {
            $this->_blDontShowEmptyCats = false;
            if ( $blDontShowEmptyCats = $this->getConfig()->getConfigParam( 'blDontShowEmptyCategories' ) ) {
                $this->_blDontShowEmptyCats = $blDontShowEmptyCats;
            }
        }
        return $this->_blDontShowEmptyCats;
    }

    /**
     * Returns shop id in trusted shops
     *
     * @return string
     */
    public function getTrustedShopId()
    {
        if ( $this->_sTrustedShopId == null && ( $aTrustedShopIds = $this->getConfig()->getConfigParam( 'iShopID_TrustedShops' ) ) ) {
            $iLangId = oxLang::getInstance()->getBaseLanguage();
            $this->_sTrustedShopId = $aTrustedShopIds[$iLangId];
        }
        return $this->_sTrustedShopId;
    }

    /**
     * Returns if language should be loaded
     *
     * @return bool
     */
    public function isLanguageLoaded()
    {
        if ( $this->_blLoadLanguage == null ) {
            $this->_blLoadLanguage = false;
            if ( $blLoadLanguage = $this->getConfig()->getConfigParam( 'bl_perfLoadLanguages' ) ) {
                $this->_blLoadLanguage = $blLoadLanguage;
            }
        }
        return $this->_blLoadLanguage;
    }

    /**
     * Returns show/hide top navigation of categories
     *
     * @return bool
     */
    public function showTopCatNavigation()
    {
        if ( $this->_blShowTopCatNav == null ) {
            $this->_blShowTopCatNav = false;
            if ( $blShowTopCatNav = $this->getConfig()->getConfigParam( 'blTopNaviLayout' ) ) {
                $this->_blShowTopCatNav = $blShowTopCatNav;
            }
        }
        return $this->_blShowTopCatNav;
    }

    /**
     * Returns item count in top navigation of categories
     *
     * @return integer
     */
    public function getTopNavigationCatCnt()
    {
        if ( $this->_iTopCatNavItmCnt == null ) {
            $iTopCatNavItmCnt = $this->getConfig()->getConfigParam( 'iTopNaviCatCount' );
            $this->_iTopCatNavItmCnt = $iTopCatNavItmCnt ? $iTopCatNavItmCnt : 5;
        }
        return $this->_iTopCatNavItmCnt;
    }

    /**
     * Returns active charset
     *
     * @return string
     */
    public function getCharSet()
    {
        if ( $this->_sCharSet == null ) {
            $this->_sCharSet = oxLang::getInstance()->translateString( 'charset' );
        }
        return $this->_sCharSet;
    }

    /**
     * Returns shop version
     *
     * @return string
     */
    public function getShopVersion()
    {
        if ( $this->_sVersion == null ) {
            $this->_sVersion = $this->getConfig()->getActiveShop()->oxshops__oxversion->value;
        }
        return $this->_sVersion;
    }

    /**
     * Returns shop edition
     *
     * @return string
     */
    public function getShopEdition()
    {
        return $this->getConfig()->getActiveShop()->oxshops__oxedition->value;
    }

    /**
     * Returns shop full edition
     *
     * @return string
     */
    public function getShopFullEdition()
    {
        $sEdition = $this->getShopEdition();
        $sFullEdition = "Community Edition";
        if ($sEdition == "PE")
            $sFullEdition = "Professional Edition";

        if ($sEdition == "EE")
            $sFullEdition = "Enterprise Edition";

        return $sFullEdition;
    }


    /**
     * Returns if current shop is demoshop
     *
     * @return string
     */
    public function isDemoVersion()
    {
        if ( $this->_blDemoVersion == null ) {
            $this->_blDemoVersion = $this->getConfig()->detectVersion() == 1;
        }
        return $this->_blDemoVersion;
    }

    /**
     * Returns RSS links
     *
     * @return array
     */
    public function getRssLinks()
    {
        return $this->_aRssLinks;
    }

    /**
     * Returns if sorting is active and can be displayed
     *
     * @return bool
     */
    public function isSortingActive()
    {
        return $this->_blActiveSorting;
    }

    /**
     * Template variable getter. Returns sorting columns
     *
     * @return array
     */
    public function getSortColumns()
    {
        return $this->_aSortColumns;
    }

    /**
     * Template variable getter. Returns string after the list is ordered by
     *
     * @return array
     */
    public function getListOrderBy()
    {
        return $this->_sListOrderBy;
    }

    /**
     * Template variable getter. Returns list order direction
     *
     * @return array
     */
    public function getListOrderDirection()
    {
        return $this->_sListOrderDir;
    }

    /**
     * Template variable getter. Returns meta description
     *
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->_sMetaDescription;
    }

    /**
     * Template variable getter. Returns meta keywords
     *
     * @return string
     */
    public function getMetaKeywords()
    {
        return $this->_sMetaKeywords;
    }

    /**
     * Get active language
     *
     * @return object
     */
    public function getActCurrency()
    {
        return $this->_oActCurrency;
    }

    /**
     * Active language setter
     *
     * @param object $oCur
     *
     * @return object
     */
    public function setActCurrency( $oCur )
    {
        $this->_oActCurrency = $oCur;
    }

    /**
     * Template variable getter. Returns article list count in comparison
     *
     * @return integer
     */
    public function getCompareItemsCnt()
    {
        return $this->_iCompItemsCnt;
    }

    /**
     * Articlelist count in comparison setter
     *
     * @param integer $iCount
     *
     * @return integer
     */
    public function setCompareItemsCnt( $iCount )
    {
        $this->_iCompItemsCnt = $iCount;
    }

    /**
     * Template variable getter. Returns user name of searched wishlist
     *
     * @return string
     */
    public function getWishlistName()
    {
        return $this->_sWishlistName;
    }

    /**
     * Sets user name of searched wishlist
     *
     * @param string $sName
     *
     * @return null
     */
    public function setWishlistName( $sName )
    {
        $this->_sWishlistName = $sName;
    }

    /**
     * Template variable getter. Returns header menue list
     *
     * @return array
     */
    public function getMenueList()
    {
        return $this->_aMenueList;
    }

    /**
     * Header menue list setter
     *
     * @param array $aMenue
     *
     * @return null
     */
    public function setMenueList( $aMenue )
    {
        $this->_aMenueList = $aMenue;
    }

    /**
     * Returns if tags will be edit
     *
     * @return bool
     */
    public function getEditTags()
    {
    }

    /**
     * Template variable getter. Returns search string
     *
     * @return string
     */
    public function getRecommSearch()
    {
        return null;
    }

    /**
     * Template variable getter. Returns review user id
     *
     * @return string
     */
    public function getReviewUserId()
    {
    }

    /**
     * Template variable getter. Returns payment id
     *
     * @return string
     */
    public function getPaymentList()
    {
    }

    /**
     * Template variable getter. Returns active recommendation lists
     *
     * @return string
     */
    public function getActiveRecommList()
    {
    }

    /**
     * Template variable getter. Returns accessoires of article
     *
     * @return object
     */
    public function getAccessoires()
    {
    }

    /**
     * Template variable getter. Returns crosssellings
     *
     * @return object
     */
    public function getCrossSelling()
    {
    }

    /**
     * Template variable getter. Returns similar article list
     *
     * @return object
     */
    public function getSimilarProducts()
    {
    }

    /**
     * Template variable getter. Returns list of customer also bought thies products
     *
     * @return object
     */
    public function getAlsoBoughtThiesProducts()
    {
    }

    /**
     * Return the active article id
     *
     * @return string | bool
     */
    public function getArticleId()
    {
    }

    /**
     * Active category setter
     *
     * @param oxcategory $oCategory active category
     *
     * @return null
     */
    public function setActiveCategory( $oCategory )
    {
        $this->_oActCategory = $oCategory;
    }

    /**
     * Active category setter
     *
     * @param oxcategory $oCategory active category
     *
     * @return null
     */
    public function getActiveCategory()
    {
        return $this->_oActCategory;
    }

    /**
     * Should "More tags" link be visible.
     *
     * @return bool
     */
    public function isMoreTagsVisible()
    {
        return false;
    }

    /**
     * Returns current view title. Default is null
     *
     * @return null
     */
    public function getTitle()
    {
    }

    /**
     * Template variable getter. Returns if newsletter can be displayed (for _right.tpl)
     *
     * @return integer
     */
    public function showNewsletter()
    {
        if ( $this->_iNewsStatus === null) {
            return 1;
        }
        return $this->_iNewsStatus;
    }

    /**
     * Sets if to show newsletter
     *
     * @param bool $blShow
     *
     * @return null
     */
    public function setShowNewsletter( $blShow )
    {
        $this->_iNewsStatus = $blShow;
    }

    /**
     * Template variable getter. Returns shop logo
     *
     * @return string
     */
    public function getShopLogo()
    {
        return $this->_sShopLogo;
    }

    /**
     * Sets shop logo
     *
     * @param string $sLogo
     *
     * @return null
     */
    public function setShopLogo( $sLogo )
    {
        $this->_sShopLogo = $sLogo;
    }

}
