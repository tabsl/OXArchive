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
 * $Id: list_review.php 14022 2008-11-06 13:40:14Z arvydas $
 */

/**
 * user list "view" class.
 * @package admin
 */
class List_Review extends oxAdminList
{
    /**
     * Executes parent method parent::render(), passes data to Smarty engine
     * and returns name of template file "list_review.tpl".
     *
     * @return string
     */
    public function render()
    {
        $this->_oList = oxNew( "oxlist", "core" );
        $this->_oList->setSqlLimit( 0, 5000 );
        $this->_oList->init( "oxreview" );

        $aWhere = $this->buildWhere();
        //$aWhere['oxreviews.oxlang'] = $this->_iEditLang;

        $sSql = $this->_buildSelectString( $this->_oList->getBaseObject() );
        $sSql = $this->_prepareWhereQuery( $aWhere, $sSql );
        $sSql = $this->_prepareOrderByQuery( $sSql );
        $sSql = $this->_changeselect( $sSql );
        $this->_oList->selectString( $sSql );

        parent::render();

        $aWhere = oxConfig::getParameter( "where");
        if ( is_array( $aWhere ) ) {
            foreach ( $aWhere as $sField => $sValue ) {
                $this->_aViewData["where"]->{str_replace( '.', '__', $sField )} = $sValue;
            }
        }

        $this->_aViewData["menustructure"] =  $this->getNavigation()->getDomXml()->documentElement->childNodes;

        return "list_review.tpl";
    }

    /**
     * Builds and returns array of SQL WHERE conditions.
     *
     * @return array
     */
    public function buildWhere()
    {
        parent::buildWhere();

        $sLangTag = oxLang::getInstance()->getLanguageTag($this->_iEditLang);
        $sArtTitleField = 'oxarticles.oxtitle';

        // if searching in article title and in lang id > 0, adding lang tag to article title field name
        if ( $sLangTag && $this->_aWhere[$sArtTitleField] ) {
            $this->_aWhere[$sArtTitleField.$sLangTag] = $this->_aWhere[$sArtTitleField];
            unset( $this->_aWhere[$sArtTitleField] );
        }

        return $this->_aWhere;
    }

    /**
     * Returns select query string
     *
     * @param object $oObject list item object
     *
     * @return string
     */
    protected function _buildSelectString( $oObject = null )
    {
        $sArtTable = getViewName('oxarticles');
        $sSql  = "select oxreviews.oxid, oxreviews.oxcreate, oxreviews.oxtext, oxreviews.oxobjectid, oxarticles.oxtitle".oxLang::getInstance()->getLanguageTag($this->_iEditLang)." as oxtitle from oxreviews left join $sArtTable as oxarticles on oxarticles.oxid=oxreviews.oxobjectid and 'oxarticle'=oxreviews.oxtype where 1";
        $sSql .= " and oxreviews.oxlang = '" . $this->_iEditLang . " ' ";

        return $sSql;
    }

    /**
     * Adds filtering conditions to query string
     *
     * @param array  $aWhere filter conditions
     * @param string $sSql   query string
     *
     * @return string
     */
    protected function _prepareWhereQuery( $aWhere, $sSql )
    {
        $sSql = parent::_prepareWhereQuery( $aWhere, $sSql );
        return " $sSql and oxarticles.oxid is not null ";
    }

    /**
     * Adds order by to SQL query string.
     *
     * @param string $sSql sql string
     *
     * @return string
     */
    protected function _prepareOrderByQuery( $sSql = null )
    {
        if ( $sSort = oxConfig::getParameter( "sort" ) ) {
            $sSql .= " order by $sSort ";
        }

        return $sSql;
    }
}