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
 * @copyright � OXID eSales AG 2003-2008
 * $Id: oxseoencodervendor.php 14368 2008-11-26 07:36:13Z vilma $
 */

/**
 * Seo encoder base
 *
 * @package core
 */
class oxSeoEncoderVendor extends oxSeoEncoder
{
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
            self::$_instance = oxNew("oxSeoEncoderVendor");
        }
        return self::$_instance;
    }

    /**
     * Returns part of SEO url excluding path
     *
     * @param oxVendor $oVendor vendor object
     * @param int      $iLang   language
     *
     * @return string
     */
    public function getVendorUri( $oVendor, $iLang = null )
    {
        if (!isset($iLang)) {
            $iLang = $oVendor->getLanguage();
        }
        // load from db
        if ( !( $sSeoUrl = $this->_loadFromDb( 'oxvendor', $oVendor->getId(), $iLang ) ) ) {

            if ($iLang != $oVendor->getLanguage()) {
                $sId = $oVendor->getId();
                if ($sId == 'root') {
                    $oVendor = oxVendor::getRootVendor( $iLang );
                } else {
                    $oVendor = oxNew('oxvendor');
                    $oVendor->loadInLang($iLang, $sId);
                }
            }

            $sSeoUrl = '';
            if ( $oVendor->getId() != 'root' ) {
                $sSeoUrl .= $this->getVendorUri( oxVendor::getRootVendor( $iLang ), $iLang );
            }

            $sSeoUrl .= $this->_prepareTitle( $oVendor->oxvendor__oxtitle->value .'/' );
            $sSeoUrl  = $this->_getUniqueSeoUrl( $sSeoUrl, '/', $oVendor->getId() );

            // save to db
            $this->_saveToDb( 'oxvendor', $oVendor->getId(), $oVendor->getStdLink(), $sSeoUrl, $iLang );
        }
        return $sSeoUrl;
    }

    /**
     * Returns vendor SEO url for specified page
     *
     * @param oxvendor $oVendor vendor object
     * @param int      $iPage   page tu prepare number
     * @param int      $iLang   language
     * @param bool     $blFixed fixed url marker (default is false)
     *
     * @return string
     */
    public function getVendorPageUrl( $oVendor, $iPage, $iLang = null, $blFixed = false )
    {
        if (!isset($iLang)) {
            $iLang = $oVendor->getLanguage();
        }
        $sStdUrl = $oVendor->getStdLink() . '&amp;pgNr=' . $iPage;
        $sParams = sprintf( "%0" . ceil( $this->_iCntPages / 10 + 1 ) . "d", $iPage + 1 );

        $sStdUrl = $this->_trimUrl( $sStdUrl, $iLang );
        $sSeoUrl = $this->getVendorUri( $oVendor, $iLang ) . $sParams . "/";

        return $this->_getFullUrl( $this->_getPageUri( $oVendor, 'oxvendor', $sStdUrl, $sSeoUrl, $sParams, $iLang, $blFixed ), $iLang );
    }

    /**
     * Encodes vendor categoru URLs into SEO format
     *
     * @param oxvendor $oVendor Vendor object
     * @param int      $iLang   language
     *
     * @return null
     */
    public function getVendorUrl( $oVendor, $iLang = null )
    {
        if (!isset($iLang)) {
            $iLang = $oVendor->getLanguage();
        }
        return $this->_getFullUrl( $this->getVendorUri( $oVendor, $iLang ), $iLang );
    }
}
