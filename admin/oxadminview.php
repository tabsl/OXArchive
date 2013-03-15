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
 * $Id: oxadminview.php 14839 2008-12-19 10:22:19Z arvydas $
 */

/**
 * Main "view" class.
 * Organizes multilanguage, parameters fetching, DB managing, file checking
 * and processing, etc.
 * @package admin
 */
class oxAdminView extends oxView
{
    /**
     * Fixed types - enums in database.
     *
     * @var array
     */
    protected $_aSumType = array(
                                0 => 'abs',
                                1 => '%',
                                2 => 'itm'
                                );

    /**
     * Current class template name.
     *
     * @var string
     */
    protected $_sThisTemplate = null;

    /**
     * Override this in list class to show other tab from beginning
     * (default 0 - the first tab).
     *
     * @var array
     */
    protected $_iDefEdit = 0;

    /**
     * Navigation tree object
     *
     * @var oxnavigationtree
     */
    protected static $_oNaviTree = null;

    /**
     * Objects editing language (default 0).
     *
     * @var integer
     */
    protected $_iEditLang   = 0;

    /**
     * Active shop title
     *
     * @var string
     */
    protected $_sShopTitle = " - ";

    /**
     * Shop Version
     *
     * @var string
     */
    protected $_sShopVersion = null;

    /**
     * Shop dynamic pages url
     *
     * @var string
     */
    protected $_sServiceUrl = null;

    /**
     * Creates oxshop object and loads shop data, sets title of shop
     */
    public function __construct()
    {
        $myConfig = $this->getConfig();
        $myConfig->setConfigParam( 'blAdmin', true );

        if ( $sShopID = $myConfig->getShopId() ) {
            $oShop = oxNew( 'oxshop' );
            if ( $oShop->load( $sShopID ) ) {

                // passing shop info
                $this->_sShopTitle   = $oShop->oxshops__oxname->value;
                $this->_sShopVersion = $oShop->oxshops__oxversion->value;

            }
        }
    }

    /**
     * Sets some shop configuration parameters (such as language),
     * creates some list object (depends on subclass) and executes
     * parent method parent::Init().
     *
     * @return null
     */
    public function init()
    {
        $myConfig = $this->getConfig();

        // authorization check
        if ( !$this->_authorize() ) {
            oxUtils::getInstance()->redirect( 'index.php' );
        }

        /*
        // module check
        foreach ( $this->_aReqModLic as $sModule ) {
            if ( !$myConfig->hasModule( $sModule ) ) {
                die( 'this module don\'t have license.' );
            }
        }*/

        // language handling
        $this->_iEditLang = oxLang::getInstance()->getEditLanguage();
        oxLang::getInstance()->setBaseLanguage();

        parent::init();

            $this->_aViewData['malladmin'] = oxSession::getVar( 'malladmin' );
    }

    /**
     * Sets some global parameters to Smarty engine (such as self link, etc.), returns
     * modified shop object.
     *
     * @param object $oShop Object to modify some parameters
     *
     * @return object
     */
    public function addGlobalParams( $oShop = null)
    {
        $mySession = $this->getSession();
        $myConfig  = $this->getConfig();

        $oShop = parent::addGlobalParams( $oShop );

        // override cause of admin dir
        $sURL = $myConfig->getConfigParam( 'sShopURL' ). $myConfig->getConfigParam( 'sAdminDir' );

        if ($myConfig->getConfigParam('sAdminSSLURL'))
            $sURL = $myConfig->getConfigParam('sAdminSSLURL');

        $oViewConf = $this->getViewConfig();
        $oViewConf->setViewConfigParam( 'selflink', $sURL.'/index.php' );
        $oViewConf->setViewConfigParam( 'ajaxlink', str_replace( '&amp;', '&', $mySession->url( $sURL.'/oxajax.php' ) ) );
        $oViewConf->setViewConfigParam( 'sServiceUrl', $this->getServiceUrl() );
        $oViewConf->setViewConfigParam( 'blLoadDynContents', $myConfig->getConfigParam( 'blLoadDynContents' ) );
        $oViewConf->setViewConfigParam( 'sShopCountry', $myConfig->getConfigParam( 'sShopCountry' ) );

        if ( $sURL = $myConfig->getConfigParam( 'sAdminSSLURL') ) {
            $oViewConf->setViewConfigParam( 'selflink', $sURL.'/index.php' );
            $oViewConf->setViewConfigParam( 'ajaxlink', str_replace( '&amp;', '&', $mySession->url( $sURL.'/oxajax.php' ) ) );
        }

        // set langugae in admin
        $iDynInterfaceLanguage = $myConfig->getConfigParam( 'iDynInterfaceLanguage' );
        //$this->_aViewData['adminlang'] = isset( $iDynInterfaceLanguage )?$iDynInterfaceLanguage:$myConfig->getConfigParam( 'iAdminLanguage' );
        $this->_aViewData['adminlang'] = isset( $iDynInterfaceLanguage )?$iDynInterfaceLanguage:oxLang::getInstance()->getTplLanguage();
        $this->_aViewData['charset']   = oxLang::getInstance()->translateString( 'charset' );

        return $oShop;
    }

    /**
     * Returns service URL
     *
     * @return string
     */
    public function getServiceUrl( $sLangAbbr=null )
    {
        if ( !empty($this->_sServiceUrl) )
            return $this->_sServiceUrl;

        $myConfig = $this->getConfig();



            $sUrl = 'http://admin.oxid-esales.com/CE/';

        $sShopVersionNr = $this->_getShopVersionNr();
        $sCountry = $this->_getCountryByCode( $myConfig->getConfigParam( 'sShopCountry' ) );

        if (!$iLang) {
            $iLang = oxLang::getInstance()->getTplLanguage();
            $aLanguages = oxLang::getInstance()->getLanguageArray();
            $sLangAbbr = $aLanguages[$iLang]->abbr;
        }

        $sUrl .= "{$sShopVersionNr}/{$sCountry}/{$sLangAbbr}/";

        $this->_sServiceUrl = $sUrl;

        return $this->_sServiceUrl;
    }

    /**
     * Returns shop version
     *
     * @return string
     */
    protected function _getShopVersionNr()
    {
        $myConfig = $this->getConfig();

        if ( $sShopID = $myConfig->getShopId() ) {
            $sQ = "select oxversion from oxshops where oxid = '$sShopID' ";
            $sVersion = oxDb::getDb()->getOne( $sQ );
        }

        $sVersion = preg_replace( "/(^[^0-9]+)(.+)$/", "$2", $sVersion );
        return trim( $sVersion );
    }

    /**
     * Sets-up navigation parameters
     *
     * @param string $sNode active view id
     *
     * @return null
     */
    protected function _setupNavigation( $sNode )
    {
        // navigation according to class
        if ( $sNode ) {

            $myAdminNavig = $this->getNavigation();

            // active tab
            $iActTab = oxConfig::getParameter( 'actedit' );
            $iActTab = $iActTab?$iActTab:$this->_iDefEdit;

            $sActTab = $iActTab?"&actedit=$iActTab":'';

            // store navigation history
            $this->_addNavigationHistory($sNode);

            // list url
            $this->_aViewData['listurl'] = $myAdminNavig->getListUrl( $sNode ).$sActTab;

            // edit url
            $this->_aViewData['editurl'] = $myAdminNavig->getEditUrl( $sNode, $iActTab ).$sActTab;
        }
    }

    /**
     * Store navigation history parameters to cookie
     *
     * @param string $sNode active view id
     *
     * @return null
     */
    protected function _addNavigationHistory( $sNode )
    {
        // store navigation history
        $aHistory = explode('|',oxUtilsServer::getInstance()->getOxCookie('oxidadminhistory'));
        if(!is_array($aHistory)) {
            $aHistory = array();
        }

        if(!in_array($sNode,$aHistory)) {
            $aHistory[] = $sNode;
        }

        oxUtilsServer::getInstance()->setOxCookie('oxidadminhistory',implode('|',$aHistory));
    }

    /**
     * Executes parent method parent::render(), passes configuration data to
     * Smarty engine.
     *
     * @return string
     */
    public function render()
    {
        $sReturn = parent::render();

        $myConfig = $this->getConfig();

        // sets up navigation data
        $this->_setupNavigation( oxConfig::getParameter( 'cl' ) );

        // active object id
        $sOxId = oxConfig::getParameter( 'oxid' );
        $this->_aViewData['oxid'] = ( !$sOxId )?-1:$sOxId;
        // add Sumtype to all templates
        $this->_aViewData['sumtype'] = $this->_aSumType;

        // active shop title
        $this->_aViewData['actshop'] = $this->_sShopTitle;
        $this->_aViewData["shopid"]  = $myConfig->getShopId();

        // loading active shop
        if ( oxSession::getVar( 'actshop' ) ) {
            // load object
            $oShop = oxNew( 'oxshop' );
            $oShop->load( oxSession::getVar( 'actshop' ) );
            $this->_aViewData['actshopobj'] =  $oShop;
        }

        // add language data to all templates
        $this->_aViewData['actlang']      = $iLanguage = oxLang::getInstance()->getBaseLanguage();
        $this->_aViewData['editlanguage'] = $this->_iEditLang;
        $this->_aViewData['languages'] = oxLang::getInstance()->getLanguageArray( $iLanguage );

        // setting maximum upload size
        list( $this->_aViewData['iMaxUploadFileSize'], $this->_aViewData['sMaxFormattedFileSize']) = $this->_getMaxUploadFileInfo( @ini_get("upload_max_filesize") );

        // "save-on-tab"
        if ( !isset( $this->_aViewData['updatelist'] ) ) {
            $this->_aViewData['updatelist'] = oxConfig::getParameter( 'updatelist' );
        }

        return $sReturn;
    }

    /**
     * Returns maximum allowed size of upload file and formatted size equivalent
     *
     * @param int $iMaxFileSize recommended maximum size of file (normalu value is taken from php ini, otherwise sets 2MB)
     *
     * @return array
     */
    protected function _getMaxUploadFileInfo( $iMaxFileSize, $blFormatted = false )
    {
        $iMaxFileSize = $iMaxFileSize?$iMaxFileSize:'2M';

        // processing config
        $iMaxFileSize = trim( $iMaxFileSize );
        $sParam = strtolower( $iMaxFileSize{ strlen( $iMaxFileSize )-1 } );
        switch( $sParam ) {
            case 'g':
                $iMaxFileSize *= 1024;
            case 'm':
                $iMaxFileSize *= 1024;
            case 'k':
                $iMaxFileSize *= 1024;
        }

        // formatting
        $aMarkers = array ( 'KB', 'MB', 'GB' );
        $sFormattedMaxSize = '';

        $iSize = floor( $iMaxFileSize / 1024 );
        while ( $iSize && current( $aMarkers ) ) {
            $sFormattedMaxSize = $iSize . current( $aMarkers );
            $iSize = floor( $iSize / 1024 );
            next( $aMarkers );
        }

        return array( $iMaxFileSize, $sFormattedMaxSize );
    }

    /**
     * Object saving function. currently used for autosaving feature.
     *
     * @return string
     */
    public function autosave()
    {
        $aAutosave = oxConfig::getParameter( 'autosave' );

        // not missing params ?
        if ( is_array( $aAutosave ) && isset( $aAutosave['oxid'] ) && isset( $aAutosave['cl'] ) ) {
            // autosaving feature
            $sReturn = '';
            foreach ( $aAutosave as $sVarName => $sVarValue ) {
                if ( $sVarValue ) {
                    if ( $sVarName == 'cl' ) {
                        $sReturn = "$sVarValue?$sReturn";
                    } else {
                        $sReturn .= "&$sVarName=$sVarValue";
                    }
                }
            }
            return "$sReturn&updatelist=1";
        }
    }

    /**
     * If autosave is on, calls atosaveve function
     *
     * @return string
     */
    public function save()
    {

        // currently this method is used for autosave
        return $this->autosave();
    }


    /**
     * Checks if current $sUserId user is not an admin and checks if user is able to be edited by logged in user.
     * This method does not perform full rights check.
     *
     * @param string $sUserId user id
     *
     * @return bool
     */
    protected function _allowAdminEdit( $sUserId )
    {

        //otherwise return true
        return true;
    }

    /**
     * Get english country name by country iso alpha 2 code
     *
     * @return boolean
     */
    protected function _getCountryByCode( $sCountryCode )
    {
        $myConfig = $this->getConfig();

        //default country
        $sCountry = 'international';

        if ( !empty($sCountryCode) ) {
            $sQ = "select oxtitle_1 from oxcountry where oxisoalpha2 = '$sCountryCode' ";
            $sCountry = oxDb::getDb()->getOne( $sQ );
        }

        return strtolower( $sCountry );
    }

    /**
     * performs authorization of admin user
     *
     * @return boolean
     */
    protected function _authorize()
    {
        return ( bool ) ( count( oxUtilsServer::getInstance()->getOxCookie() ) && oxUtils::getInstance()->checkAccessRights() );
    }

    /**
     * Returns navigation object
     *
     * @return oxnavigationtree
     */
    public function getNavigation()
    {
        if ( self::$_oNaviTree == null ) {
            self::$_oNaviTree = oxNew( 'oxnavigationtree' );
            self::$_oNaviTree->init();
        }
        return self::$_oNaviTree;
    }

    /**
     * Current view ID getter helps to identify navigation position
     *
     * @return string
     */
    public function getViewId()
    {
        $sClassName = strtolower( get_class( $this ) );
        return $this->getNavigation()->getClassId( $sClassName );
    }

    /**
     * Changing active shop
     *
     * @return string
     */
    public function chshp()
    {
        $sActShop = oxConfig::getParameter( 'actshop' );
        $this->getConfig()->setShopId( $sActShop );
        oxSession::setVar( "shp", $sActShop);
        oxSession::setVar( 'currentadminshop', $sActShop );
    }

    /**
     * Marks seo entires as expired (oxseo::oxexpired = 2), leans up tag couds cache
     *
     * @return null
     */
    public function resetSeoData( $sShopId )
    {
        $oEncoder = oxSeoEncoder::getInstance();
        $oEncoder->markAsExpired( null, $sShopId, 2 );

        // resetting tag cache
        $oTagCloud = oxNew('oxtagcloud');
        $oTagCloud->resetTagCache();
    }
}
