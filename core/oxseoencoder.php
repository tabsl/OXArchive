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
 * @package core
 * @copyright © OXID eSales AG 2003-2009
 * $Id: oxseoencoder.php 14649 2008-12-11 15:36:13Z arvydas $
 */

/**
 * Seo encoder base
 *
 * @package core
 */
class oxSeoEncoder extends oxSuperCfg
{
    /**
     * Strings that cannot be used in SEO URLs as this may cause
     * compatability/access problems
     *
     * @var array
     */
    protected static $_aReservedWords = array( 'admin' );

    /**
     * SEO separator.
     *
     * @var string
     */
    protected static $_sSeparator = null;

    /**
     * SEO id length.
     *
     * @var integer
     */
    protected $_iIdLength = 255;

    /**
     * SEO prefix.
     *
     * @var string
     */
    protected static $_sPrefix = null;

    /**
     * Added parameters.
     *
     * @var string
     */
    protected $_sAddParams = null;

    /**
     * Singleton instance.
     */
    protected static $_instance = null;

    /**
     * Singleton method
     *
     * @return oxseoencoder
     */
    public static function getInstance()
    {
        if (!self::$_instance) {
            self::$_instance = oxNew("oxSeoEncoder");
        }
        return self::$_instance;
    }

    /**
     * SEO encoder constructor
     */
    public function __construct()
    {
        $myConfig = $this->getConfig();
        if (!self::$_sSeparator) {
            $this->setSeparator( $myConfig->getConfigParam( 'sSEOSeparator' ) );
        }
        if (!self::$_sPrefix) {
            $this->setPrefix( $myConfig->getConfigParam( 'sSEOuprefix' ) );
        }
        $this->setReservedWords( $myConfig->getConfigParam( 'aSEOReservedWords' ) );
    }

    /**
     * Moves current seo record to seo history table
     *
     * @param string $sId     object id
     * @param int    $iShopId active shop id
     * @param int    $iLang   object language
     * @param string $sType   object type (if you pass real object - type is not necessary)
     * @param string $sNewId  new object id, mostly used for static url updates (optional)
     *
     * @return null
     */
    protected function _copyToHistory( $sId, $iShopId, $iLang, $sType = null, $sNewId = null )
    {
        $sObjectid = $sNewId?"'$sNewId'":'oxobjectid';
        $sType     = $sType?"oxtype = {$sType} and":'';

        // moving
        $sSub = "select {$sObjectid}, MD5( LOWER( oxseourl ) ), oxshopid, oxlang, now() from oxseo where {$sType} oxobjectid = {$sId} and oxshopid = {$iShopId} and oxlang = {$iLang} limit 1";
        $sQ   = "replace oxseohistory ( oxobjectid, oxident, oxshopid, oxlang, oxinsert ) {$sSub}";
        oxDb::getDb()->execute( $sQ );
    }

    /**
     * Returns string for SEO url with specific parameters (language,
     * currency and active shop)
     *
     * @return string
     */
    protected function _getAddParams()
    {
        // performance
        if ( $this->_sAddParams === null ) {
            $this->_sAddParams = $this->_getAddParamsFnc( oxConfig::getParameter('currency'), $this->getConfig()->getShopId() );
        }
        return $this->_sAddParams;
    }

    /**
     * Returns string for SEO url with specific parameters (language,
     * currency and active shop)
     *
     * @param integer $iCur     shop currency
     * @param mixed   $iActShop active shop id
     *
     * @return string
     */
    protected function _getAddParamsFnc( $iCur, $iActShop )
    {
        // according to new functionality we don't need this ??
        $this->_sAddParams = '';
        $sSep = '?';
        if ( $iCur ) {
            $this->_sAddParams .= $sSep . 'cur=' . $iCur;
            $sSep = '&amp;';
        }


        return $this->_sAddParams;
    }

    /**
     * Returns dynamic object SEO URI
     *
     * @param string $sStdUrl standart url
     * @param string $sSeoUrl seo uri
     * @param int    $iLang   active language
     *
     * @return string
     */
    protected function _getDynamicUri( $sStdUrl, $sSeoUrl, $iLang )
    {
        $iShopId = $this->getConfig()->getShopId();

        $sStdUrl   = $this->_trimUrl( $sStdUrl, $iLang );
        $sObjectId = md5( strtolower( $iShopId . $sStdUrl ) );
        $sSeoUrl   = $this->_prepareTitle( $sSeoUrl );

        //load details link from DB
        $sOldSeoUrl = $this->_loadFromDb( 'dynamic', $sObjectId, $iLang );
        if ( $sOldSeoUrl === $sSeoUrl ) {
            $sSeoUrl = $sOldSeoUrl;
        } else {

            if ( $sOldSeoUrl ) { // old must be transferred to history
                $oDb = oxDb::getDb();
                $this->_copyToHistory( $oDb->quote( $sObjectId ), $oDb->quote( $iShopId ), $iLang, $oDb->quote( 'dynamic' ) );
            }

            // creating unique
            $sSeoUrl = $this->_getUniqueSeoUrl( $sSeoUrl, null, $sObjectId );

            // inserting
            $this->_saveToDb( 'dynamic', $sObjectId, $sStdUrl, $sSeoUrl, $iLang, $iShopId );
        }

        return $sSeoUrl;
    }

    /**
     * Returns part of url defining active language
     *
     * @param int $iObjectLang object language id
     *
     * @return string
     */
    public function getLanguageParam( $iObjectLang, $blForce = false )
    {
        $iDefLang = (int) $this->getConfig()->getConfigParam( 'iDefSeoLang' );
        $aLangIds = oxLang::getInstance()->getLanguageIds();
        $sLang = '';

        if ( $blForce || ( $iObjectLang != $iDefLang && isset( $aLangIds[$iObjectLang] ) ) ) {

            $sLang = $aLangIds[$iObjectLang] . '/';
        }
        return $sLang;
    }

    /**
     * Returns SEO url with shop's path + additional params ( oxseoencoder:: _getAddParams)
     *
     * @param string $sSeoUrl seo URL
     * @param int    $iLang   active language
     *
     * @return string
     */
    protected function _getFullUrl( $sSeoUrl, $iLang )
    {
        return $this->getConfig()->getShopURL() . $this->getLanguageParam( (int) $iLang ) . $sSeoUrl . $this->_getAddParams();
    }

    /**
     * _getSeoIdent returns seo ident for db search
     *
     * @param string $sSeoUrl seo url
     * @param int    $iLang   active language
     *
     * @access protected
     *
     * @return string
     */
    protected function _getSeoIdent( $sSeoUrl, $iLang )
    {
        return md5( strtolower( $this->getLanguageParam( $iLang, true ) . $sSeoUrl ) );
    }

    /**
     * Returns SEO static uri
     *
     * @param string $sStdUrl standard page url
     * @param int    $iShopId active shop id
     * @param int    $iLang   active language
     *
     * @return string
     */
    protected function _getStaticUri( $sStdUrl, $iShopId, $iLang )
    {
        $sIdent  = md5( strtolower( $iShopId . $this->_trimUrl( $sStdUrl, $iLang ) ) );
        return $this->_loadFromDb( 'static', $sIdent, $iLang );
    }

    /**
     * _getUniqueSeoUrl returns possibly modified url
     * for not to be same as already existing in db
     *
     * @param string $sSeoUrl   seo url
     * @param string $sConstEnd target "extension" - ".html" or "/"
     * @param string $sObjectId current object id, used to skip self in query
     *
     * @access protected
     *
     * @return string
     */
    protected function _getUniqueSeoUrl( $sSeoUrl, $sConstEnd = null, $sObjectId = null )
    {
        if ($sConstEnd === null) {
            $aMatched = array();
            if ( preg_match('/\.html?$/i', $sSeoUrl, $aMatched ) ) {
                $sConstEnd = $aMatched[0];
            } else {
                if ($sSeoUrl{strlen($sSeoUrl)-1} != '/') {
                    $sSeoUrl .= '/';
                }
                $sConstEnd = '/';
            }
        }

        $sBaseSeoUrl = $sSeoUrl;
        if ( $sConstEnd && substr( $sSeoUrl, 0 - strlen( $sConstEnd ) ) == $sConstEnd ) {
            $sBaseSeoUrl = substr( $sSeoUrl, 0, strlen( $sSeoUrl ) - strlen( $sConstEnd ) );
        }

        $oDb = oxDb::getDb();
        $iShopId = $this->getConfig()->getShopId();
        $iCnt = 0;
        $iLang = oxLang::getInstance()->getBaseLanguage();
        $sCheckSeoUrl = $this->_trimUrl( $sSeoUrl, $iLang );
        $sQ = "select 1 from oxseo where oxshopid = '{$iShopId}'";

        // skipping self
        if ( $sObjectId ) {
            $sQ .= " and oxobjectid != '{$sObjectId}'";
        }

        while ( $oDb->getOne( $sQ ." and oxident='".$this->_getSeoIdent( $sCheckSeoUrl, $iLang )."' " ) ) {

            $sAdd = self::$_sSeparator . self::$_sPrefix;
            if ( $iCnt ) {
                $sAdd .= self::$_sSeparator . $iCnt;
            }
            ++$iCnt;

            $sSeoUrl = $sBaseSeoUrl . $sAdd . $sConstEnd;
            $sCheckSeoUrl = $this->_trimUrl( $sSeoUrl, $iLang );
        }
        return $sSeoUrl;
    }

    /**
     * _loadFromDb loads data from oxseo table if exists
     * returns oxseo url
     *
     * @param string $sType               object type
     * @param string $sId                 object identifier
     * @param int    $iLang               active language id
     * @param mixed  $iShopId             active shop id
     * @param string $sParams             additional seo params. optional (mostly used for db indexing)
     * @param bool   $blStrictParamsCheck strict parameters check
     *
     * @access protected
     *
     * @return string || false
     */
    protected function _loadFromDb( $sType, $sId, $iLang, $iShopId = null, $sParams = null, $blStrictParamsCheck = true)
    {
        $oDb = oxDb::getDb( true );
        if ( $iShopId === null ) {
            $iShopId = $this->getConfig()->getShopId();
        }

        $iShopId = $oDb->quote( $iShopId );
        $sId   = $oDb->quote( $sId );
        $sType = $oDb->quote( $sType );
        $iLang = (int) $iLang;

        $sQ = "select oxfixed, oxseourl, oxexpired, oxtype from oxseo where oxtype = {$sType}
               and oxobjectid = {$sId} and oxshopid = {$iShopId} and oxlang = {$iLang}";

        if ($sParams) {
            if ($blStrictParamsCheck) {
                $sQ .= " and oxparams = '{$sParams}'";
            } else {
                $sQ .= " order by oxparams = '{$sParams}' desc";
            }
        }
        $sQ .= " limit 1";

        $sSeoUrl = false;
        $oRs = $oDb->execute( $sQ );
        if ( $oRs && $oRs->recordCount() > 0 && !$oRs->EOF ) {
            // moving expired static urls to history ..
            if ( $oRs->fields['oxexpired'] && ( $oRs->fields['oxtype'] == 'static' || $oRs->fields['oxtype'] == 'dynamic' ) ) {
                // if expired - copying to history, marking as not expired
                $this->_copyToHistory( $sId, $iShopId, $iLang );
                $oDb->execute( "update oxseo set oxexpired = 0 where oxobjectid = {$sId} and oxlang = '{$iLang}' " );
                $sSeoUrl = $oRs->fields['oxseourl'];
            } elseif ( !$oRs->fields['oxexpired'] || $oRs->fields['oxfixed'] ) {
                // if seo url is available and is valid
                $sSeoUrl = $oRs->fields['oxseourl'];
            }
        }
        return $sSeoUrl;
    }

    /**
     * Prepares and returns formatted object SEO id
     *
     * @param string $sTitle         Original object title
     * @param bool   $blSkipTruncate Truncate title into defined lenght or not
     *
     * @return string
     */
    protected function _prepareTitle( $sTitle, $blSkipTruncate = false )
    {
        // decoding entities
        $sTitle = $this->encodeString( $sTitle );

        // basic string preparation
        $sTitle = strip_tags( $sTitle );
        $sSeparator = self::$_sSeparator;
        $sPrefix     = self::$_sPrefix;
        // 'fixing' reserved words
        foreach ( self::$_aReservedWords as $sWord ) {
            // this probably possible to do in one regexp
            $sTitle = preg_replace( array( "/(\s$sWord)$/i", "/^($sWord\s)/i", "/(\s$sWord\s)/i", "/^($sWord)$/i",
                                           "/(\/$sWord)$/i", "/^($sWord\/)/i", "/(\/$sWord\/)/i"),
                                    " $1{$sSeparator}{$sPrefix}{$sSeparator} ", $sTitle );
        }

        // if found ".html" at the end - removing it temporary
        $sExt = '';
        $aMatched = array();
        if ( preg_match( '/\.html?$/i', $sTitle, $aMatched ) ) {
            $sExt   = substr( $sTitle, 0 - strlen( $aMatched[0] ) );
            $sTitle = substr( $sTitle, 0, strlen( $sTitle ) - strlen( $aMatched[0] ) );
        }

        // smart truncate
        if ( !$blSkipTruncate && strlen( $sTitle ) > $this->_iIdLength ) {

            if ( ( $iFirstSpace = strstr( substr( $sTitle, $this->_iIdLength ), ' ' ) ) ) {
                $sTitle = substr( $sTitle, 0, $this->_iIdLength + $iFirstSpace );
            }
        }

        // removing any special characters
        $sRegExp = '/[^A-Za-z0-9'.preg_quote( self::$_sSeparator, '/').'\/]+/';
        $sTitle  = trim( preg_replace( $sRegExp, self::$_sSeparator, $sTitle ), self::$_sSeparator );

        // binding ".html" back
        $sTitle .= $sExt;

        // SEO id is empty ?
        if ( !$sTitle ) {
            $sTitle = $this->_prepareTitle( self::$_sPrefix );
        }

        // cleaning
        return preg_replace( array( '|//+|', '/' . preg_quote( self::$_sSeparator . self::$_sSeparator, '/' ) .'+/' ),
                             array( '/', self::$_sSeparator ), $sTitle );
    }


    /**
     * _saveToDb saves values to seo table
     *
     * @param string $sType        url type (static, dynamic, oxarticle etc)
     * @param string $sObjectId    object identifier
     * @param string $sStdUrl      standard url
     * @param string $sSeoUrl      seo url
     * @param int    $iLang        active object language
     * @param mixed  $iShopId      active object shop id
     * @param bool   $blFixed      seo entry marker. if true, entry should not be automatically changed
     * @param string $sKeywords    object keywords
     * @param string $sDescription object description
     * @param string $sParams      additional seo params. optional (mostly used for db indexing)
     *
     * @access protected
     *
     * @return void
     */
    protected function _saveToDb( $sType, $sObjectId, $sStdUrl, $sSeoUrl, $iLang, $iShopId = null, $blFixed = 0, $sKeywords = '', $sDescription = '', $sParams = null )
    {
        $oDb = oxDb::getDb( true );
        if ( $iShopId === null ) {
            $iShopId = $this->getConfig()->getShopId();
        }

        $iShopId = $oDb->quote( $iShopId );

        $sObjectId   = $oDb->quote( $sObjectId );
        $sType = $oDb->quote( $sType );

        $iLang = (int) $iLang;

        $sStdUrl = $this->_trimUrl( $sStdUrl, $iLang );
        $sSeoUrl = $this->_trimUrl( $sSeoUrl, $iLang );

        $sIdent = $this->_getSeoIdent( $sSeoUrl, $iLang );

        $sStdUrl = $oDb->quote( $sStdUrl );
        $sSeoUrl = $oDb->quote( $sSeoUrl );

        $blFixed = (int) $blFixed;
        // transferring old url, thus current url will be regenerated
        $sQ  = "select oxfixed, oxexpired, ( oxstdurl like {$sStdUrl} and oxexpired != 2 ) as samestdurl, oxseourl like {$sSeoUrl} as sameseourl from oxseo where oxtype = {$sType} and oxobjectid = {$sObjectId} and oxshopid = {$iShopId} and oxlang = {$iLang} ";
        $sQ .= $sParams?" and oxparams = '{$sParams}' " : '';
        $sQ .= "limit 1";

        $oRs = $oDb->execute( $sQ );
        if ( $oRs && $oRs->recordCount() > 0 && !$oRs->EOF ) {

            if ( $oRs->fields['samestdurl'] && $oRs->fields['sameseourl'] && $oRs->fields['oxexpired'] ) {
                // nothing was changed - setting expired status back to 0
                return $oDb->execute( "update oxseo set oxexpired = 0 where oxtype = {$sType} and oxobjectid = {$sObjectId} and oxshopid = {$iShopId} and oxlang = {$iLang} limit 1" );
            } elseif ( $oRs->fields['oxexpired'] && !$oRs->fields['oxfixed'] ) {
                // copy to history
                $this->_copyToHistory( $sObjectId, $iShopId, $iLang, $sType );
            }
        }

        $sKeywords = $sKeywords?$oDb->quote( htmlentities( $this->encodeString( strip_tags( $sKeywords ) ) ) ):"''";
        $sDescription = $sDescription?$oDb->quote( htmlentities( strip_tags( $sDescription ) ) ):"''";

        // inserting new or updating
        $sParams = $sParams ? $oDb->quote( $sParams ) :'""';
        $sQ  = "replace into oxseo
                    (oxobjectid, oxident, oxshopid, oxlang, oxstdurl, oxseourl, oxtype, oxfixed, oxexpired, oxkeywords, oxdescription, oxparams)
                values
                    ( {$sObjectId}, '$sIdent', {$iShopId}, {$iLang}, {$sStdUrl}, {$sSeoUrl}, {$sType}, '$blFixed', '0', {$sKeywords}, {$sDescription}, $sParams )";
        return $oDb->execute( $sQ );
    }

    /**
     * Removes shop path part and session id from given url
     *
     * @param string $sUrl  url to clean bad chars
     * @param int    $iLang active language
     *
     * @access protected
     *
     * @return string
     */
    protected function _trimUrl( $sUrl, $iLang )
    {
        $sUrl = str_replace( $this->getConfig()->getShopURL(), '', $sUrl );

        if ( ( $sLangParam = $this->getLanguageParam( $iLang ) ) ) {
            $sUrl = preg_replace( "'^".preg_quote( $sLangParam, "'")."'", '', $sUrl );
        }

        return preg_replace( '/sid=[a-z0-9\.]+&?(amp;)?/i', '', $sUrl );
    }

    /**
     * Replaces special chars in text
     *
     * @param string $sString string to encode
     *
     * @return string
     */
    public function encodeString( $sString )
    {
        // decoding entities
        $sString = html_entity_decode( $sString );

        $aReplaceChars = $this->getConfig()->getConfigParam( 'aSeoReplaceChars' );
        $sString = str_replace( array_keys( $aReplaceChars ), array_values( $aReplaceChars ), $sString );

        // special chars
        $aReplaceWhat = array( '/&amp;/', '/&quot;/', '/&#039;/', '/&lt;/', '/&gt;/' );
        return str_replace( $aReplaceWhat, '', $sString );
    }

    /**
     * Sets SEO separator
     *
     * @param string $sSeparator SEO seperator
     *
     * @return null
     */
    public function setSeparator( $sSeparator = null )
    {
        self::$_sSeparator = $sSeparator;
        if ( !self::$_sSeparator ) {
            self::$_sSeparator = '-';
        }
    }

    /**
     * Sets SEO prefix
     *
     * @param string $sPrefix SEO prefix
     *
     * @return null
     */
    public function setPrefix( $sPrefix )
    {
        if ($sPrefix) {
            self::$_sPrefix = $sPrefix;
        } else {
            self::$_sPrefix = 'oxid';
        }
    }

    /**
     * sets seo id length
     *
     * @param string $iIdlength id length
     *
     * @return null
     */
    public function setIdLength( $iIdlength = null )
    {
        if ( isset( $iIdlength ) ) {
            $this->_iIdLength = $iIdlength;
        }
    }

    /**
     * Sets array of words which must be checked before building seo url
     *
     * @param array $aReservedWords reserved words
     *
     * @return null
     */
    public function setReservedWords( $aReservedWords )
    {
        self::$_aReservedWords = array_merge( self::$_aReservedWords, $aReservedWords );
    }


    /**
     * Marks object seo records as expired
     *
     * @param string $sId      changed object id. If null is passed, object dependency is not checked
     * @param int    $iShopId  active shop id. Shop id must be passed uf you want to do shop level update (default null)
     * @param int    $iExpStat expiration status: 1 - standard expiration, 2 - seo primary language id expiration
     * @param int    $iLang    active language (optiona;)
     * @param string $sParams  additional params
     *
     * @return null
     */
    public function markAsExpired( $sId, $iShopId = null, $iExpStat = 1, $iLang = null, $sParams = null )
    {
        $sWhere  = $sId ? "where oxobjectid = '{$sId}'" : '';
        $sWhere .= isset( $iShopId ) ? ( $sWhere ? " and oxshopid = '{$iShopId}'" : "where oxshopid = '{$iShopId}'" ) : '';
        $sWhere .= $iLang ? ( $sWhere ? " and oxlang = '{$iLang}'" : "where oxlang = '{$iLang}'" ) : '';
        $sWhere .= $sParams ? ( $sWhere ? " and {$sParams}" : "where {$sParams}" ) : '';

        $sQ = "update oxseo set oxexpired = '{$iExpStat}' $sWhere ";
        oxDb::getDb()->execute( $sQ );
    }

    /**
     * Loads if exists or prepares and saves new seo url for passed object
     *
     * @param oxbase $oObject object to prepare seo data
     * @param string $sType   type of object (oxvendor/oxcategory)
     * @param string $sStdUrl stanradr url
     * @param string $sSeoUrl seo uri
     * @param string $sParams additional params, liek page number etc. mostly used by mysql for indexes
     * @param int    $iLang   language
     * @param bool   $blFixed fixed url marker (default is false)
     *
     * @return string
     */
    protected function _getPageUri( $oObject, $sType, $sStdUrl, $sSeoUrl, $sParams, $iLang = null, $blFixed = false )
    {
        if (!isset($iLang)) {
            $iLang = $oObject->getLanguage();
        }
        $iShopId = $this->getConfig()->getShopId();

        //load details link from DB
        if ( ( $sOldSeoUrl = $this->_loadFromDb( $sType, $oObject->getId(), $iLang, $iShopId, $sParams ) ) ) {
            if ( $sOldSeoUrl === $sSeoUrl ) {
                return $sSeoUrl;
            } else {
                $oDb = oxDb::getDb();
                $this->_copyToHistory( $oDb->quote( $oObject->getId() ), $oDb->quote( $iShopId ), $iLang, $oDb->quote( $sType ) );
            }
        }

        $this->_saveToDb( $sType, $oObject->getId(), $sStdUrl, $sSeoUrl, $iLang, $iShopId, (int) $blFixed, '', '', $sParams );

        return $sSeoUrl;
    }

    /**
     * Static url encoder
     *
     * @param array $aStaticUrl static url info (contains standard URL and urls for each language)
     * @param int   $iShopId    active shop id
     * @param int   $iLang      active language
     *
     * @return null
     */
    public function encodeStaticUrls( $aStaticUrl, $iShopId, $iLang )
    {
        $oDb = oxDb::getDb();
        $sValues = '';
        $sOldObjectId = null;

        // standard url
        $sStdUrl = $this->_trimUrl( trim( $aStaticUrl['oxseo__oxstdurl'] ), $iLang  );
        $sObjectId = $aStaticUrl['oxseo__oxobjectid'];

        if ( !$sObjectId || $sObjectId == '-1' ) {
            $sObjectId = md5( strtolower ( $iShopId.$sStdUrl ) );
        } else {
            // marking entry as needs to move to history
            $sOldObjectId = $sObjectId;

            // if std url does not match old
            if ( md5( strtolower ( $iShopId.$sStdUrl ) ) != $sObjectId ) {
                $sObjectId = md5( strtolower ( $iShopId.$sStdUrl ) );
            }
        }

        foreach ( $aStaticUrl['oxseo__oxseourl'] as $iLang => $sSeoUrl ) {

            // generating seo url
            if ( ( $sSeoUrl = trim( $sSeoUrl ) ) ) {
                $sSeoUrl = $this->_prepareTitle( $this->_trimUrl( $sSeoUrl, $iLang ) );
                $sSeoUrl = $this->_getUniqueSeoUrl( $sSeoUrl, null, $sObjectId );
            }

            if ( $sOldObjectId ) {
                // move changed records to history
                if ( !$oDb->getOne( "select ('{$sSeoUrl}' like oxseourl) & ('{$sStdUrl}' like oxstdurl) from oxseo where oxobjectid = '{$sOldObjectId}' and oxshopid = '{$iShopId}' and oxlang = '{$iLang}' " ) ) {
                    $this->_copyToHistory( $oDb->quote( $sOldObjectId ), $iShopId, $iLang, $oDb->quote( 'static' ), $sObjectId );
                }
            }

            if ( !$sSeoUrl || !$sStdUrl ) {
                continue;
            }

            $sIdent = $this->_getSeoIdent( $sSeoUrl, $iLang );

            if ( $sValues ) {
                $sValues .= ', ';
            }

            $sValues .= "( '{$sObjectId}', '{$sIdent}', '{$iShopId}', '{$iLang}', '$sStdUrl', '$sSeoUrl', 'static' )";
        }

        // must delete old before insert/update
        if ( $sOldObjectId ) {
            $oDb->execute( "delete from oxseo where oxobjectid in ( '{$sOldObjectId}', '{$sObjectId}' )" );
        }

        // (re)inserting
        if ( $sValues ) {

            $sQ = "insert into oxseo ( oxobjectid, oxident, oxshopid, oxlang, oxstdurl, oxseourl, oxtype ) values {$sValues} ";
            $oDb->execute( $sQ );
        }

        return $sObjectId;
    }

    /**
     * Method copies static urls from base shop to newly created
     *
     * @param int $iShopId new created shop id
     *
     * @return null
     */
    public function copyStaticUrls( $iShopId )
    {
        $iBaseShopId = $this->getConfig()->getBaseShopId();
        if ( $iShopId != $iBaseShopId ) {
            foreach (array_keys(oxLang::getInstance()->getLanguageIds()) as $iLang) {
                $iLang = (int) $iLang;
                $sPrfx = $this->getLanguageParam($iLang, true);
                $sQ = "insert into oxseo ( oxobjectid, oxident, oxshopid, oxlang, oxstdurl, oxseourl, oxtype )
                       select MD5( LOWER( CONCAT( '{$iShopId}', oxstdurl ) ) ), MD5( LOWER(  CONCAT( '{$sPrfx}', oxseourl ) ) ),
                       '$iShopId', oxlang, oxstdurl, oxseourl, oxtype from oxseo where oxshopid = '{$iBaseShopId}' and oxtype = 'static' and oxlang='$iLang' ";
                oxDb::getDb()->execute( $sQ );
            }
        }
    }

    /**
     * Returns static url for passed standard link (if available)
     *
     * @param string $sStdUrl standard Url
     * @param int    $iLang   active language (optional). default null
     * @param int    $iShopId active shop id (optional). default null
     *
     * @return string
     */
    public function getStaticUrl( $sStdUrl, $iLang = null, $iShopId = null )
    {
        if (!isset($iShopId)) {
            $iShopId = oxConfig::getInstance()->getShopId();
        }
        if (!isset($iLang)) {
            $iLang   = oxLang::getInstance()->getEditLanguage();
        }

        $sFullUrl = '';
        if ( ( $sSeoUrl = $this->_getStaticUri( $sStdUrl, $iShopId, $iLang ) ) ) {
            $sFullUrl = $this->_getFullUrl( $sSeoUrl, $iLang );
        }
        return $sFullUrl;
    }

    /**
     * Adds new seo entry to db
     *
     * @param string $sObjectId    objects id
     * @param int    $iShopId      shop id
     * @param int    $iLang        objects language
     * @param string $sStdUrl      default url
     * @param string $sSeoUrl      seo url
     * @param string $sType        object type
     * @param bool   $blFixed      marker to keep seo config unchangeable
     * @param string $sKeywords    seo keywords
     * @param string $sDescription seo description
     * @param string $sParams      additional seo params. optional (mostly used for db indexing)
     *
     * @return null
     */
    public function addSeoEntry( $sObjectId, $iShopId, $iLang, $sStdUrl, $sSeoUrl, $sType, $blFixed = 1, $sKeywords = '', $sDescription = '', $sParams = '' )
    {
        $sSeoUrl = $this->_getUniqueSeoUrl( $this->_prepareTitle( $this->_trimUrl( $sSeoUrl, $iLang ) ), null, $sObjectId );
        $this->_saveToDb( $sType, $sObjectId, $sStdUrl, $sSeoUrl, $iLang, $iShopId, $blFixed, $sKeywords, $sDescription, $sParams );
    }

    /**
     * Removes seo entry from db
     *
     * @param string $sObjectId objects id
     * @param int    $iShopId   shop id
     * @param int    $iLang     objects language
     * @param string $sType     object type
     *
     * @return null
     */
    public function deleteSeoEntry( $sObjectId, $iShopId, $iLang, $sType )
    {
        $sQ = "delete from oxseo where oxobjectid = '{$sObjectId}' and oxshopid = '{$iShopId}' and oxlang = '{$iLang}' and oxtype = '{$sType}' ";
        oxDb::getDb()->execute( $sQ );
    }

    /**
     * Returns meta information for preferred object
     *
     * @param string $sObjectId information object id
     * @param string $sMetaType metadata type - "oxkeywords", "oxdescription"
     * @param int    $iShopId   active shop id
     * @param int    $iLang     active language
     *
     * @return string
     */
    public function getMetaData( $sObjectId, $sMetaType, $iShopId = null, $iLang = null )
    {
        $iShopId = ( !isset( $iShopId ) ) ? oxConfig::getInstance()->getShopId():$iShopId;
        $iLang   = ( !isset( $iLang ) ) ? oxLang::getInstance()->getTplLanguage():$iLang;

        return oxDb::getDb()->getOne( "select {$sMetaType} from oxseo where oxobjectid = '{$sObjectId}' and oxshopid = '{$iShopId}' and oxlang = '{$iLang}'" );
    }

    /**
     * getDynamicUrl acts similar to static urls,
     * except, that dynamic url are not shown in admin
     * and they can be reencoded by providing new seo url
     *
     * @param string $sStdUrl standard url
     * @param string $sSeoUrl part of URL query which will be attached to standard shop url
     * @param int    $iLang   active language
     *
     * @access public
     *
     * @return string
     */
    public function getDynamicUrl( $sStdUrl, $sSeoUrl, $iLang )
    {
        return $this->_getFullUrl( $this->_getDynamicUri( $sStdUrl, $sSeoUrl, $iLang ), $iLang );
    }

    /**
     * return params, that are not encoded in seourl
     *
     * @ param string $sSeoUrl
     * NOTE: not used
    public function getSimplifyUriParams( $sSeoUrl )
    {
        $sFullUrl = $sSeoUrl;
        $oSeoSecoder = oxNew('oxseodecoder');
        if ($iPos = strpos($sSeoUrl, '?')) {
            $sSeoUrl = substr($sSeoUrl, 0, $iPos);
        }
        if (!($aHaveParams = $oSeoSecoder->decodeUrl($sSeoUrl))) {
            return $sFullUrl;
        }
        $aParams = $oSeoSecoder->parseStdUrl($sFullUrl);

        $aParams = array_diff($aParams, $aHaveParams);

        $sSep = '?';
        foreach ($aParams as $key => $value) {
            $sSeoUrl .= "$sSep$key=$value";
            $sSep = '&amp;';
        }

        return $sSeoUrl;
    }
    */
}
