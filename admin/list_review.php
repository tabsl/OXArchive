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
 * @copyright (C) OXID eSales AG 2003-2009
 * @version OXID eShop CE
 * $Id: list_review.php 16302 2009-02-05 10:18:49Z rimvydas.paskevicius $
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
        //$this->_oList->setAssignCallback( array( oxNew("List_Review"), 'formatArticleTitle'));
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
     * Returns select query string
     *
     * @param object $oObject list item object
     *
     * @return string
     */
    protected function _buildSelectString( $oObject = null )
    {
        $sArtTable = getViewName('oxarticles');
        $sLangTag = oxLang::getInstance()->getLanguageTag( $this->_iEditLang );

        $sSql = "select oxreviews.oxid, oxreviews.oxcreate, oxreviews.oxtext, oxreviews.oxobjectid, oxarticles.oxparentid, oxarticles.oxtitle{$sLangTag} as oxtitle, oxarticles.oxvarselect{$sLangTag} as oxvarselect, oxparentarticles.oxtitle{$sLangTag} as parenttitle,
                   concat( oxarticles.oxtitle{$sLangTag}, if(isnull(oxparentarticles.oxtitle{$sLangTag}), '', oxparentarticles.oxtitle{$sLangTag}), oxarticles.oxvarselect_1) as arttitle from oxreviews
                 left join $sArtTable as oxarticles on oxarticles.oxid=oxreviews.oxobjectid and 'oxarticle' = oxreviews.oxtype
                 left join $sArtTable as oxparentarticles on oxparentarticles.oxid = oxarticles.oxparentid
                 where 1 and oxreviews.oxlang = '{$this->_iEditLang}' ";
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
        $sArtTitleField = 'oxarticles.oxtitle';
        $sSqlForTitle = null;
        $sLangTag = oxLang::getInstance()->getLanguageTag( $this->_iEditLang );

        $sSql = parent::_prepareWhereQuery( $aWhere, $sSql );

        // if searching in article title field, updating sql for this case
        if ( $this->_aWhere[$sArtTitleField] ) {
            $sSqlForTitle = " (CONCAT( oxarticles.oxtitle{$sLangTag}, if(isnull(oxparentarticles.oxtitle{$sLangTag}), '', oxparentarticles.oxtitle{$sLangTag}), oxarticles.oxvarselect{$sLangTag})) ";
            $sSql = preg_replace( "/oxarticles\.oxtitle\s+like/", "$sSqlForTitle like", $sSql );
        }

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