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
 * $Id: shop_seo.php 14839 2008-12-19 10:22:19Z arvydas $
 */

/**
 * Admin shop system setting manager.
 * Collects shop system settings, updates it on user submit, etc.
 * Admin Menu: Main Menu -> Core Settings -> System.
 * @package admin
 */
class Shop_Seo extends Shop_Config
{
    /**
     * Active seo url id
     */
    protected $_sActSeoObject = null;

    /**
     * Executes parent method parent::render() and returns name of template
     * file "shop_system.tpl".
     *
     * @return string
     */
    public function render()
    {
        parent::render();

        //
        $oShop = $this->_aViewData["edit"];

        $oShop->loadInLang( $this->_iEditLang, $oShop->oxshops__oxid->value );

        // load object in other languages
        $oOtherLang = $oShop->getAvailableInLangs();
        if (!isset($oOtherLang[$this->_iEditLang])) {
            // echo "language entry doesn't exist! using: ".key($oOtherLang);
            $oShop->loadInLang( key($oOtherLang), $oShop->oxshops__oxid->value );
        }

        $aLang = array_diff ( oxLang::getInstance()->getLanguageNames(), $oOtherLang);
        if ( count( $aLang))
            $this->_aViewData["posslang"] = $aLang;

        foreach ( $oOtherLang as $id => $language) {
            $oLang = new oxStdClass();
            $oLang->sLangDesc = $language;
            $oLang->selected = ($id == $this->_iEditLang);
            $this->_aViewData["otherlang"][$id] = clone $oLang;
        }

        // loading static seo urls
        $sQ = "select oxstdurl, oxobjectid from oxseo where oxtype='static' and oxshopid='".$oShop->getId()."' group by oxobjectid order by oxstdurl";

        $oList = oxNew( 'oxlist' );
        $oList->init( 'oxbase', 'oxseo' );
        $oList->selectString( $sQ );

        $this->_aViewData['aStaticUrls'] = $oList;

        // loading active url info
        $this->_loadActiveUrl( $oShop->getId() );

        return "shop_seo.tpl";
    }

    /**
     * Loads and sets active url info to view
     *
     * @param int $iShopId active shop id
     *
     * @return null
     */
    protected function _loadActiveUrl( $iShopId )
    {
        $sActObject = null;
        if ( $this->_sActSeoObject ) {
            $sActObject = $this->_sActSeoObject;
        } elseif ( is_array( $aStatUrl = oxConfig::getParameter( 'aStaticUrl' ) ) ) {
            $sActObject = $aStatUrl['oxseo__oxobjectid'];
        }

        if ( $sActObject && $sActObject != '-1' ) {
            $this->_aViewData['sActSeoObject'] = $sActObject;

            $sQ = "select oxseourl, oxlang from oxseo where oxobjectid = '$sActObject' and oxshopid = '{$iShopId}'";
            $oRs = oxDb::getDb(true)->execute( $sQ );
            if ( $oRs != false && $oRs->recordCount() > 0 ) {
                while ( !$oRs->EOF ) {
                    $aSeoUrls[$oRs->fields['oxlang']] = array( $oRs->fields['oxobjectid'], $oRs->fields['oxseourl'] );
                    $oRs->moveNext();
                }
                $this->_aViewData['aSeoUrls'] = $aSeoUrls;
            }
        }
    }

    /**
     * Saves changed shop configuration parameters.
     *
     * @return mixed
     */
    public function save()
    {

        // saving config params
        $this->saveConfVars();

        $soxId = oxConfig::getParameter( 'oxid' );
        $aParams = oxConfig::getParameter( 'editval' );
        $aConfParams = oxConfig::getParameter( 'confstrs' );

        $oEncoder = oxSeoEncoder::getInstance();

        // on default language change all shop SEO urls must be revalidated
        $iDefLang = $this->getConfig()->getConfigParam( 'iDefSeoLang' );
        $iUserLang = (int) ( ( isset( $aConfParams['iDefSeoLang'] ) )? $aConfParams['iDefSeoLang'] : 0 );
        if ( $iDefLang != $iUserLang ) {
            $this->resetSeoData( $soxId );
        }

        $oShop = oxNew( 'oxshop' );
        $oShop->load( $soxId );

        //assigning values
        $oShop->assign( $aParams );
        $oShop->setLanguage( $this->_iEditLang );
        $oShop->save();

        oxUtils::getInstance()->rebuildCache();

        // saving static url changes
        if ( is_array( $aStaticUrl = oxConfig::getParameter( 'aStaticUrl' ) ) ) {
            $this->_sActSeoObject = $oEncoder->encodeStaticUrls( $aStaticUrl, $oShop->getId(), $this->_iEditLang );
        }

        return $this->autosave();
    }

    /**
     * Dropping SEO ids
     *
     * @return null
     */
    public function dropSeoIds()
    {
        $sQ = 'delete from oxseo where oxshopid = "'.oxConfig::getInstance()->getShopId().'" and oxtype != "static" and oxfixed != 1';
        oxDB::getDb()->execute( $sQ );
    }

    /**
     * Deletes static url
     *
     * @return null
     */
    public function deleteStaticUrl()
    {
        if ( is_array( $aStaticUrl = oxConfig::getParameter( 'aStaticUrl' ) ) ) {
            if ( ( $sObjectid = $aStaticUrl['oxseo__oxobjectid'] ) && $sObjectid != '-1' ) {
                // active shop id
                $soxId = oxConfig::getParameter( 'oxid' );
                oxDb::getDb()->execute( "delete from oxseo where oxobjectid = '{$sObjectid}' and oxshopid = '{$soxId}'" );
            }
        }
    }
}
