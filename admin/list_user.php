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
 * $Id: list_user.php 13900 2008-10-30 08:17:40Z arvydas $
 */

/**
 * user list "view" class.
 * @package admin
 */
class List_User extends oxAdminList
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
        $this->_oList->init( "oxuser" );

        $aWhere = $this->buildWhere();

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

        return "list_user.tpl";
    }

    /**
     * Adds where condition to SQL
     */
    protected function _prepareWhereQuery( $aWhere, $sSql )
    {
        $sQ = parent::_prepareWhereQuery( $aWhere, $sSql );


        return $sQ;
    }

    /**
     * Returns select query string
     */
    protected function _buildSelectString( $oObject = null )
    {
        return 'select oxuser.oxid, oxuser.oxfname, oxuser.oxlname, oxuser.oxusername, oxuser.oxregister from oxuser where 1 ';
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