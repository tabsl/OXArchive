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
 * @copyright © OXID eSales AG 2003-2008
 * $Id: vendor_main.php 14014 2008-11-06 13:26:22Z arvydas $
 */

/**
 * Admin vendor main screen.
 * Performs collection and updating (on user submit) main item information.
 * @package admin
 */
class Vendor_Main extends oxAdminDetails
{
    /**
     * Executes parent method parent::render(),
     * and returns name of template file
     * "vendor_main.tpl".
     *
     * @return string
     */
    public function render()
    {
        parent::render();

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
            $oVendor = oxNew( "oxvendor" );
            $oVendor->loadInLang( $this->_iEditLang, $soxId );

            $oOtherLang = $oVendor->getAvailableInLangs();
            if (!isset($oOtherLang[$this->_iEditLang])) {
                // echo "language entry doesn't exist! using: ".key($oOtherLang);
                $oVendor->loadInLang( key($oOtherLang), $soxId );
            }
            $this->_aViewData["edit"] =  $oVendor;

            // category tree
            $sChosenArtCat = $this->_getCategoryTree( "artcattree", oxConfig::getParameter( "artcat"));

            //Disable editing for derived articles
            if ($oVendor->isDerived())
               $this->_aViewData['readonly'] = true;

            // remove already created languages
            $aLang = array_diff ( oxLang::getInstance()->getLanguageNames(), $oOtherLang);
            if ( count( $aLang))
                $this->_aViewData["posslang"] = $aLang;

            foreach ( $oOtherLang as $id => $language) {
                $oLang= new oxStdClass();
                $oLang->sLangDesc = $language;
                $oLang->selected = ($id == $this->_iEditLang);
                $this->_aViewData["otherlang"][$id] = clone $oLang;
            }
        }

        if ( oxConfig::getParameter("aoc") ) {

            $aColumns = array();
            include_once 'inc/'.strtolower(__CLASS__).'.inc.php';
            $this->_aViewData['oxajax'] = $aColumns;

            return "popups/vendor_main.tpl";
        }
        return "vendor_main.tpl";
    }

    /**
     * Saves selection list parameters changes.
     *
     * @return mixed
     */
    public function save()
    {

        $soxId   = oxConfig::getParameter( "oxid");
        $aParams = oxConfig::getParameter( "editval");

        if ( !isset( $aParams['oxvendor__oxactive']))
            $aParams['oxvendor__oxactive'] = 0;

            // shopid
            $sShopID = oxSession::getVar( "actshop");
            $aParams['oxvendor__oxshopid'] = $sShopID;

        $oVendor = oxNew( "oxvendor" );

        if ( $soxId != "-1")
            $oVendor->loadInLang( $this->_iEditLang, $soxId );
        else {
            $aParams['oxvendor__oxid'] = null;
        }


        //$aParams = $oVendor->ConvertNameArray2Idx( $aParams);
        $oVendor->setLanguage(0);
        $oVendor->assign( $aParams);
        $oVendor->setLanguage($this->_iEditLang);
        $oVendor = oxUtilsFile::getInstance()->processFiles( $oVendor );
        $oVendor->save();
        $this->_aViewData["updatelist"] = "1";

        // set oxid if inserted
        if ( $soxId == "-1")
            oxSession::setVar( "saved_oxid", $oVendor->oxvendor__oxid->value);

        return $this->autosave();
    }

    /**
     * Saves selection list parameters changes in different language (eg. english).
     *
     * @return mixed
     */
    public function saveinnlang()
    {
        $soxId      = oxConfig::getParameter( "oxid");
        $aParams    = oxConfig::getParameter( "editval");

        if ( !isset( $aParams['oxvendor__oxactive']))
            $aParams['oxvendor__oxactive'] = 0;

            // shopid
            $sShopID = oxSession::getVar( "actshop");
            $aParams['oxvendor__oxshopid'] = $sShopID;

        $oVendor = oxNew( "oxvendor" );

        if ( $soxId != "-1")
            $oVendor->loadInLang( $this->_iEditLang, $soxId );
        else {
            $aParams['oxvendor__oxid'] = null;
        }


        //$aParams = $oVendor->ConvertNameArray2Idx( $aParams);
        $oVendor->setLanguage(0);
        $oVendor->assign( $aParams);
        $oVendor->setLanguage($this->_iEditLang);
        $oVendor = oxUtilsFile::getInstance()->processFiles( $oVendor );
        $oVendor->save();
        $this->_aViewData["updatelist"] = "1";

        // set oxid if inserted
        if ( $soxId == "-1")
            oxSession::setVar( "saved_oxid", $oVendor->oxvendor__oxid->value);

        return $this->autosave();
    }

    /**
     * Removes selected vendor from article.
     *
     * @return null
     */
    public function removearticle()
    {
        $aRemoveArt = oxConfig::getParameter( "artinthiscat");
        $soxId      = oxConfig::getParameter( "oxid");


        if ( isset( $aRemoveArt) && $aRemoveArt) {
            $sSelect =  "update oxarticles set oxvendorid = null where oxid in ( ";
            $blSep = false;
            foreach ($aRemoveArt as $sRem) {
                if ( $blSep)
                    $sSelect .= ",";
                $sSelect .= "'$sRem'";
                $blSep = true;
            }
            $sSelect .= ")";

            oxDb::getDb()->Execute( $sSelect);

                // resetting article count
                oxUtilsCount::getInstance()->resetVendorArticleCount($soxId);
        }
    }

    /**
     * Adds vendor to article.
     *
     * @return null
     */
    public function addarticle()
    {
        $aAddArticle = oxConfig::getParameter( "allartincat");
        $soxId       = oxConfig::getParameter( "oxid");


        if ( isset( $aAddArticle) && $aAddArticle) {
            $sSelect =  "update oxarticles set oxvendorid = '$soxId' where oxid in ( ";
            $blSep = false;
            foreach ($aAddArticle as $sRem) {
                if ( $blSep)
                    $sSelect .= ",";
                $sSelect .= "'$sRem'";
                $blSep = true;
            }
            $sSelect .= ")";

            oxDb::getDb()->Execute( $sSelect);

                // resetting article count
                oxUtilsCount::getInstance()->resetVendorArticleCount($soxId);
        }
    }
}
