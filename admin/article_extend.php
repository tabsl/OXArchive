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
 * $Id: article_extend.php 14767 2008-12-16 12:14:11Z vilma $
 */

/**
 * Admin article extended parameters manager.
 * Collects and updates (on user submit) extended article properties ( such as
 * weight, dimensions, purchase Price and etc.). There is ability to assign article
 * to any chosen article group.
 * Admin Menu: Manage Products -> Articles -> Extended.
 * @package admin
 */
class Article_Extend extends oxAdminDetails
{
    /**
     * Collects available article axtended parameters, passes them to
     * Smarty engine and returns tamplate file name "article_extend.tpl".
     *
     * @return string
     */
    public function render()
    {
        parent::render();

        $this->_aViewData['edit'] = $oArticle = oxNew( 'oxarticle' );

        $soxId = oxConfig::getParameter( 'oxid' );
        $sCatView = getViewName( 'oxcategories' );

        // all categories
        if ( $soxId != "-1" && isset( $soxId ) ) { // load object
            $oArticle->loadInLang( $this->_iEditLang, $soxId );


            // load object in other languages
            $oOtherLang = $oArticle->getAvailableInLangs();
            if (!isset($oOtherLang[$this->_iEditLang])) {
                // echo "language entry doesn't exist! using: ".key($oOtherLang);
                $oArticle->loadInLang( key($oOtherLang), $soxId );
            }

            foreach ( $oOtherLang as $id => $language) {
                $oLang= new oxStdClass();
                $oLang->sLangDesc = $language;
                $oLang->selected = ($id == $this->_iEditLang);
                $this->_aViewData["otherlang"][$id] =  clone $oLang;
            }

            // variant handling
            if ( $oArticle->oxarticles__oxparentid->value) {
                $oParentArticle = oxNew( 'oxarticle' );
                $oParentArticle->load( $oArticle->oxarticles__oxparentid->value);
                $this->_aViewData["parentarticle"] = $oParentArticle;
                $this->_aViewData["oxparentid"]    = $oArticle->oxarticles__oxparentid->value;
            }

            $sO2CView = getViewName('oxobject2category');
        }

        if ( oxConfig::getParameter("aoc") ) {

            $aColumns = array();
            include_once 'inc/'.strtolower(__CLASS__).'.inc.php';
            $this->_aViewData['oxajax'] = $aColumns;

            return "popups/article_extend.tpl";
        }


            $oDB = oxDb::GetDB();
            $myConfig = oxConfig::getInstance();
            $suffix = ($this->_iEditLang)?"_$this->_iEditLang":"";

            $aList = array();

            $iArtCnt = $myConfig->getParameter( "iArtCnt");

            // only load them when DB is small as it anyway makes no sense to display 1000 of e.g. 100.000
            //if( $iArtCnt < $myConfig->getConfigParam( 'iMaxArticles' ) ) {

            $oArt = new stdClass();
            $oArt->oxarticles__oxid = new oxField("");
            $oArt->oxarticles__oxartnum = new oxField("");
            $oArt->oxarticles__oxtitle = new oxField(" -- ");
            $aList[] = $oArt;

            // #546
            $blHideVariants = !$myConfig->getConfigParam( 'blVariantsSelection' );
            $sHideVariants  = $blHideVariants?" where oxarticles.oxparentid = '' and ":" where ";
            // FS#1780 V
            $sHideItself    = "oxarticles.oxid != '$soxId' and ";

            $sSelect =  "select oxarticles.oxid, oxarticles.oxartnum, oxarticles.oxtitle$suffix, oxarticles.oxvarselect$suffix from oxarticles $sHideVariants ";
            $sSelect .= "$sHideItself oxarticles.oxshopid = '".$myConfig->getShopId()."' order by oxartnum, oxtitle";
            $rs = $oDB->Execute( $sSelect);
            if ($rs != false && $rs->RecordCount() > 0) {
                while (!$rs->EOF) {
                    $oArt = new stdClass();    // #663
                    $oArt->oxarticles__oxid = new oxField($rs->fields[0]);
                    $oArt->oxarticles__oxnid = new oxField($rs->fields[0]);
                    $oArt->oxarticles__oxartnum = new oxField($rs->fields[1]);
                    $oArt->oxarticles__oxtitle = new oxField($rs->fields[2]);
                    if ( !$oArt->oxarticles__oxtitle->value && !$blHideVariants)
                        $oArt->oxarticles__oxtitle = new oxField($rs->fields[3]);
                    if( $oArt->oxarticles__oxid->value == $oArticle->oxarticles__oxbundleid->value) {
                        $oArt->selected = 1;
                    } else {
                        $oArt->selected = 0;
                    }
                    $aList[] = $oArt;
                    $rs->MoveNext();
                }
            }
            /*
            }
            else
            {
                $oArt = new stdClass();
                $oArt->oxarticles__oxid = new oxField("");
                $oArt->oxarticles__oxartnum = new oxField("");
                $oArt->oxarticles__oxtitle = new oxField(" not available,too many Articles ");
                $aList[] = $oArt;
            }*/
            $this->_aViewData["arttree"] =  $aList;

        //load media files
        $this->_aViewData['aMediaUrls'] = $oArticle->getMediaUrls();

        return "article_extend.tpl";
    }

    /**
     * Saves modified extended article parameters.
     *
     * @return mixed
     */
    public function save()
    {

        $soxId      = oxConfig::getParameter( "oxid");
        $aParams    = oxConfig::getParameter( "editval");
        // checkbox handling
        if ( !isset( $aParams['oxarticles__oxissearch'])) {
            $aParams['oxarticles__oxissearch'] = 0;
        }
        if ( !isset( $aParams['oxarticles__oxblfixedprice'])) {
            $aParams['oxarticles__oxblfixedprice'] = 0;
        }

        // new way of handling bundled articles
        //#1517C - remove posibility to add Bundled Product
        //$this->setBundleId($aParams, $soxId);

        // default values
        $aParams = $this->addDefaultValues( $aParams);

        $oArticle = oxNew( "oxarticle" );
        $oArticle->loadInLang( $this->_iEditLang, $soxId);

        if ( $aParams['oxarticles__oxtprice'] != $oArticle->oxarticles__oxtprice->value && $aParams['oxarticles__oxtprice'] < $oArticle->oxarticles__oxprice->value) {
            $aParams['oxarticles__oxtprice'] = $oArticle->oxarticles__oxtprice->value;
            $this->_aViewData["errorsavingtprice"] = 1;
        }

        //$aParams = $oArticle->ConvertNameArray2Idx( $aParams);
        $oArticle->setLanguage(0);
        $oArticle->assign( $aParams);
        $oArticle->setLanguage($this->_iEditLang);
        $oArticle = oxUtilsFile::getInstance()->processFiles( $oArticle );
        $oArticle->save();

        //saving media file
        $sMediaUrl  = oxConfig::getParameter( "mediaUrl");
        $sMediaDesc = oxConfig::getParameter( "mediaDesc");
        $aMediaFile = oxConfig::getInstance()->getUploadedFile( "mediaFile");

        if ($sMediaUrl || $aMediaFile['name'] || $sMediaDesc) {

            if ( !$sMediaDesc ) {
                oxUtilsView::getInstance()->addErrorToDisplay(oxLang::getInstance()->translateString('EXCEPTION_NODESCRIPTIONADDED'));
                return;
            }

            if ( !$sMediaUrl && !$aMediaFile['name'] ) {
                oxUtilsView::getInstance()->addErrorToDisplay(oxLang::getInstance()->translateString('EXCEPTION_NOMEDIAADDED'));
                return;
            }

            $oMediaUrl = oxNew("oxMediaUrl");
            $oMediaUrl->setLanguage( $this->_iEditLang );
            $oMediaUrl->oxmediaurls__oxisuploaded = new oxField( 0, oxField::T_RAW);

            //handle uploaded file
            if ($aMediaFile['name']) {
                try {
                    $sMediaUrl = oxUtilsFile::getInstance()->handleUploadedFile($aMediaFile, 'out/media/');
                    $oMediaUrl->oxmediaurls__oxisuploaded = new oxField(1, oxField::T_RAW);
                } catch (Exception $e) {
                    oxUtilsView::getInstance()->addErrorToDisplay(oxLang::getInstance()->translateString($e->getMessage()));
                    return;
                }
            }

            //save media url
            $oMediaUrl->oxmediaurls__oxobjectid = new oxField($soxId, oxField::T_RAW);
            $oMediaUrl->oxmediaurls__oxurl = new oxField($sMediaUrl, oxField::T_RAW);
            $oMediaUrl->oxmediaurls__oxdesc = new oxField(oxConfig::getParameter( "mediaDesc"), oxField::T_RAW);
            $oMediaUrl->save();

        }

        return $this->autosave();
    }

    /**
     * Deletes media url (with possible linked files)
     *
     * @return bool
     */
    public function deletemedia()
    {
        $soxId = oxConfig::getParameter( "oxid");
        $sMediaId = oxConfig::getParameter( "mediaid");
        if ($sMediaId && $soxId) {
            $oMediaUrl = oxNew("oxMediaUrl");
            $oMediaUrl->load($sMediaId);
            $oMediaUrl->delete();
        }
    }

    /**
     * Adds default values for extended article parameters. Returns modified
     * parameters array.
     *
     * @param array $aParams Article marameters array
     *
     * @return array
     */
    public function addDefaultValues( $aParams)
    {
        $aParams['oxarticles__oxexturl'] = str_replace( "http://", "", $aParams['oxarticles__oxexturl']);

        return $aParams;
    }

    /**
     * Updates existing media descriptions
     *
     * @return null
     */
    public function updateMedia()
    {
        $aMediaUrls = oxConfig::getParameter( 'aMediaUrls' );
        if ( is_array( $aMediaUrls ) ) {
            foreach ( $aMediaUrls as $sMediaId => $aMediaParams ) {
                $oMedia = oxNew("oxMediaUrl");
                if ( $oMedia->load( $sMediaId ) ) {
                    $oMedia->setLanguage(0);
                    $oMedia->assign( $aMediaParams );
                    $oMedia->setLanguage( $this->_iEditLang );
                    $oMedia->save();
                }
            }
        }
    }

}
