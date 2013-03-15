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
 * $Id: article_seo.php 13672 2008-10-25 08:56:58Z arvydas $
 */

/**
 * Article seo config class
 */
class Article_Seo extends Object_Seo
{
    /**
     * Chosen category id
     *
     * @var string
     */
    protected $_sActCatId = null;

    /**
     * Article deepest categoy nodes list
     *
     * @var oxlist
     */
    protected $_oArtCategories = null;

    /**
     * Article deepest vendor list
     *
     * @var oxlist
     */
    protected $_oArtVendors = null;

    /**
     * Active article object
     *
     * @var oxarticle
     */
    protected $_oArticle = null;

    /**
     * Loads article parameters and passes them to Smarty engine, returns
     * name of template file "article_main.tpl".
     *
     * @return string
     */
    public function render()
    {
        $oArticle = $this->_getObject( oxConfig::getParameter( 'oxid' ) );

        $this->_aViewData["edit"] = $oArticle;
        $this->_aViewData["blShowCatSelect"] = true;
        $this->_aViewData["oCategories"] = $this->_getCategoryList( $oArticle );
        $this->_aViewData["oVendors"]    = $this->_getVendorList( $oArticle );
        $this->_aViewData["sCatId"]      = $this->getActCategory();

        return parent::render();
    }

    /**
     * Returns SQL to fetch seo data
     *
     * @return string
     */
    protected function _getSeoDataSql( $oObject, $iShopId, $iLang )
    {
        $sParam = ( $sCat = $this->getActCategory() ) ? " and oxparams = '$sCat'" : '';
        $sQ = "select * from oxseo where oxobjectid = '".$oObject->getId()."' and
               oxshopid = '{$iShopId}' and oxlang = {$iLang} {$sParam} ";
        return $sQ;
    }

    /**
     * Returns list with deepest article categories
     *
     * @return oxlist
     */
    protected function _getCategoryList( $oArticle )
    {
        if ( $this->_oArtCategories === null ) {
            $this->_oArtCategories = ( $oArticle ) ? oxSeoEncoderArticle::getInstance()->getSeoCategories( $oArticle ) : false;
        }

        return $this->_oArtCategories;
    }

    /**
     * Returns list with deepest article categories
     *
     * @return oxlist
     */
    protected function _getVendorList( $oArticle )
    {
        if ( $this->_oArtVendors === null ) {
            $this->_oArtVendors = false;

            if ( $oArticle->oxarticles__oxvendorid->value ) {
                $oVendor = oxNew( 'oxvendor' );
                if ( $oVendor->loadInLang( $this->_iEditLang, $oArticle->oxarticles__oxvendorid->value ) ) {
                    $this->_oArtVendors = oxNew( 'oxList', 'oxvendor' );
                    $this->_oArtVendors[] = $oVendor;
                }
            }
        }
        return $this->_oArtVendors;
    }

    /**
     * Returns currently chosen or first from article category deepest list category parent id
     *
     * @return string
     */
    public function getActCategory()
    {
        if ( $this->_sActCatId === null) {
            $this->_sActCatId = false;

            $aSeoData = oxConfig::getParameter( 'aSeoData' );
            if ( $aSeoData && isset( $aSeoData['oxparams'] ) ) {
                $this->_sActCatId = $aSeoData['oxparams'];
            } elseif ( ( $oCats = $this->_getCategoryList( $this->_getObject( oxConfig::getParameter( 'oxid' ) ) ) ) ) {
                $oCats->rewind();
                $this->_sActCatId = $oCats->current()->oxcategories__oxrootid->value;
            }
        }

        return $this->_sActCatId;
    }

    /**
     * Returns objects seo url
     * @param oxarticle $oArticle active article object
     * @return string
     */
    protected function _getSeoUrl( $oArticle )
    {
        oxSeoEncoderArticle::getInstance()->getArticleUrl( $this->_getObject( oxConfig::getParameter( 'oxid' ) ) );
        return parent::_getSeoUrl( $oArticle );
    }

    /**
     * Returns seo object
     * @return mixed
     */
    protected function _getObject( $sOxid )
    {
        if ( $this->_oArticle === null ) {
            // load object
            $this->_oArticle = oxNew( 'oxarticle' );
            if ( !$this->_oArticle->loadInLang( $this->_iEditLang, $sOxid ) ) {
                $this->_oArticle = false;
            }
        }
        return $this->_oArticle;
    }

    /**
     * Returns url type
     * @return string
     */
    protected function _getType()
    {
        return 'oxarticle';
    }

    /**
     * Returns objects std url
     * @return string
     */
    protected function _getStdUrl( $sOxid )
    {
        $oArticle = oxNew( 'oxarticle' );
        $oArticle->loadInLang( $this->_iEditLang, $sOxid );
        return $oArticle->getLink();
    }
}