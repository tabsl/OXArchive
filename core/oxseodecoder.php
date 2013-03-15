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
 * @copyright © OXID eSales AG 2003-2008
 * $Id: oxseodecoder.php 14378 2008-11-26 13:59:41Z vilma $
 */

/**
 * Seo encoder base
 *
 * @package core
 */
class oxSeoDecoder extends oxSuperCfg
{
    /**
     * _parseStdUrl parses given url into array of params
     *
     * @param string $sUrl given url
     *
     * @access protected
     * @return array
     */
    public function parseStdUrl($sUrl)
    {
        $aRet = array();
        $sUrl = html_entity_decode($sUrl);
        if (($iPos = strpos($sUrl, '?')) !== false) {
            $aParams = explode('&', substr($sUrl, $iPos+1));
            foreach ($aParams as $sParam) {
                $aP = explode('=', $sParam);
                if (count($aP) == 2) {
                    if (($sName = trim($aP[0])) && ($sValue = trim($aP[1]))) {
                        $aRet[$sName] = rawurldecode($sValue);
                    }
                }
            }
        }
        return $aRet;
    }

    /**
     * decodeUrl decodes given url into oxid eShop required parameters
     * wich are returned as array
     *
     * @param string $sSeoUrl SEO url
     *
     * @access public
     * @return array || false
     */
    public function decodeUrl( $sSeoUrl )
    {
        $sBaseUrl = $this->getConfig()->getShopURL();
        if ( strpos( $sSeoUrl, $sBaseUrl ) === 0 ) {
            $sSeoUrl = substr( $sSeoUrl, strlen( $sBaseUrl ) );
        }
        $iShopId = $this->getConfig()->getShopId();
        $sSeoUrl = rawurldecode($sSeoUrl);

        $sKey = md5( strtolower( $sSeoUrl ) );

        $rs = oxDb::getDb(true)->Execute( "select oxstdurl, oxlang from oxseo where oxident='$sKey' and oxshopid='$iShopId' limit 1");
        if (!$rs->EOF) {
            $sStdUrl = $rs->fields['oxstdurl'];
            $iLang   = $rs->fields['oxlang'];
            $aRet = $this->parseStdUrl($sStdUrl);
            $aRet['lang'] = $iLang;
            return $aRet;
        }
        return false;
    }

     /**
     * Checks if url is stored in history table and if it was found - tryes
     * to fetch new url from seo table
     *
     * @param string $sSeoUrl SEO url
     *
     * @access public
     * @return string || false
     */
    protected function _decodeOldUrl( $sSeoUrl )
    {
        $oDb = oxDb::getDb(true);
        $sBaseUrl = $this->getConfig()->getShopURL();
        if ( strpos( $sSeoUrl, $sBaseUrl ) === 0 ) {
            $sSeoUrl = substr( $sSeoUrl, strlen( $sBaseUrl ) );
        }
        $iShopId = $this->getConfig()->getShopId();
        $sSeoUrl = rawurldecode($sSeoUrl);
        $sKey = md5( strtolower( $sSeoUrl ) );

        $sUrl = false;
        $rs = $oDb->execute( "select oxobjectid, oxlang from oxseohistory where oxident = '{$sKey}' and oxshopid = '{$iShopId}' limit 1");
        if ( !$rs->EOF ) {
            // updating hit info (oxtimestamp field will be updated automatically)
            $oDb->execute( "update oxseohistory set oxhits = oxhits + 1 where oxident = '{$sKey}' and oxshopid = '{$iShopId}' limit 1" );

            // fetching new url
            $sUrl = $oDb->getOne( "select oxseourl from oxseo where oxobjectid = '{$rs->fields['oxobjectid']}' and oxlang = '{$rs->fields['oxlang']}' and oxshopid = '{$iShopId}' " );
        }
        return $sUrl;
    }

    /**
     * processSeoCall handles Server information and passes it to decoder
     *
     * @param string $sRequest request
     * @param string $sPath    path
     *
     * @access public
     * @return void
     */
    public function processSeoCall( $sRequest = null, $sPath = null )
    {
        // first - collect needed parameters
        if ( !$sRequest ) {
            if ( isset( $_SERVER['REQUEST_URI'] ) && $_SERVER['REQUEST_URI'] ) {
                $sRequest = $_SERVER['REQUEST_URI'];
            } else {    // try something else
                $sRequest = $_SERVER['SCRIPT_URI'];
            }
        }

        $sPath = $sPath ? $sPath : str_replace( 'oxseo.php', '', $_SERVER['SCRIPT_NAME'] );
        if ( ( $sParams = $this->_getParams( $sRequest, $sPath ) ) ) {

            // in case SEO url is actual
            if ( is_array( $aGet = $this->decodeUrl( $sParams ) ) ) {
                $_GET = array_merge( $aGet, $_GET );
                oxLang::getInstance()->resetBaseLanguage();
            } elseif ( ( $sRedirectUrl = $this->_decodeOldUrl( $sParams ) ) ) {
                // in case SEO url was changed - redirecting to new location
                oxUtils::getInstance()->redirect( $this->getConfig()->getShopURL().$sRedirectUrl, false );
            } elseif ( ( $sRedirectUrl = $this->_decodeSimpleUrl( $sParams ) ) ) {
                // old type II seo urls
                oxUtils::getInstance()->redirect( $this->getConfig()->getShopURL().$sRedirectUrl, false );
            } else { // unrecognized url
                error_404_handler( $sParams );
            }
        }
    }

    /**
     * Tries to fetch SEO url according to type II seo url data. If no
     * specified data is found NULL will be returned
     *
     * @param string $sParams request params (url chunk)
     *
     * @return string
     */
    protected function _decodeSimpleUrl( $sParams )
    {
        $sLastParam = rtrim( $sParams, '/' );
        $sLastParam = substr( $sLastParam, ( ( int ) strrpos( $sLastParam, '/' ) ) - ( strlen( $sLastParam ) ) );
        $sLastParam = trim( $sParams, '/' );

        // active object id
        $sUrl = null;

        if ( $sLastParam ) {

            $sLastParam = oxDb::getDb()->quote( $sLastParam );
            $iLanguage  = oxLang::getInstance()->getBaseLanguage();

            // article ?
            if ( strpos( $sLastParam, '.htm' ) !== false ) {
                $sUrl = $this->_getObjectUrl( $sLastParam, 'oxarticles', $iLanguage, 'oxarticle' );
            } else {

                // category ?
                if ( !( $sUrl = $this->_getObjectUrl( $sLastParam, 'oxcategories', $iLanguage, 'oxcategory' ) ) ) {
                    // then maybe vendor ?
                    $sUrl = $this->_getObjectUrl( $sLastParam, 'oxvendor', $iLanguage, 'oxvendor' );
                }
            }
        }

        return $sUrl;
    }

    /**
     * Searches and returns (if available) current objects seo url
     *
     * @param string $sSeoId    ident (or last chunk of url)
     * @param string $sTable    name of table to look for data
     * @param int    $iLanguage current language identifier
     * @param string $sType     type of object to search in seo table
     *
     * @return string
     */
    protected function _getObjectUrl( $sSeoId, $sTable, $iLanguage, $sType )
    {
        $oDb     = oxDb::getDb();
        $sTable  = getViewName( $sTable );
        $sField  = "oxseoid".oxLang::getInstance()->getLanguageTag( $iLanguage );
        $sSeoUrl = null;

        try {
            if ( $sObjectId = $oDb->getOne( "select oxid from $sTable where $sField = $sSeoId" ) ) {
                $sSeoUrl = $oDb->getOne( "select oxseourl from oxseo where oxtype = '$sType' and oxobjectid = '$sObjectId' and oxlang = '$iLanguage' " );
            }
        } catch ( Exception $oEx ) {
            // in case field does not exist must catch db exception
        }

        return $sSeoUrl;
    }

    /**
     * Extracts SEO paramteters and returns as array
     *
     * @param string $sRequest request
     * @param string $sPath    path
     *
     * @return array $aParams extracted params
     */
    protected function _getParams( $sRequest, $sPath )
    {
        $sParams = preg_replace( '/\?.*/', '', $sRequest );
        $sPath   = preg_quote($sPath, '/');
        $sParams = preg_replace( "/^$sPath/", '', $sParams );

        // this should not happen on most cases, because this redirect is handled by .htaccess
        if ( $sParams && !ereg( '\.html$', $sParams ) && !ereg( '\/$', $sParams ) ) {
            oxUtils::getInstance()->redirect( $this->getConfig()->getShopURL() . $sParams . '/', false );
        }

        return $sParams;
    }

    /**
     * Searches for seo url in seo table. If not found - FALSE is returned
     *
     * @param string  $sStdUrl   standard url
     * @param integer $iLanguage language
     *
     * @return mixed
     */
    public function fetchSeoUrl( $sStdUrl, $iLanguage = null )
    {
        $oDb = oxDb::getDb( true );
        $sStdUrl = $oDb->quote( $sStdUrl );
        $iLanguage = isset( $iLanguage ) ? $iLanguage : oxLang::getInstance()->getBaseLanguage();

        $sSeoUrl = false;

        $sQ = "select oxseourl, oxlang from oxseo where oxstdurl = $sStdUrl and oxlang = '$iLanguage' limit 1";
        $oRs = $oDb->execute( $sQ );
        if ( !$oRs->EOF ) {
            $sSeoUrl = oxSeoEncoder::getInstance()->getLanguageParam( $oRs->fields['oxlang'] ).$oRs->fields['oxseourl'];
        }

        return $sSeoUrl;
    }
}
